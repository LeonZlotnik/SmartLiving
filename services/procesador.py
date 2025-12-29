import tabula
import pandas as pd
import numpy as np
from sqlalchemy import create_engine
import pymysql

def procesar_archivos(
    archivo_pdf="Cuentas.pdf",
    archivo_excel="tabla_pagos.xlsx",
    usuario="root",
    password="password",
    host="localhost",
    puerto=3305,
    base_de_datos="condominios"
):
    # -----------------------------
    # LEER PDF
    # -----------------------------
    pdfData = tabula.read_pdf(archivo_pdf, pages=1, multiple_tables=True, stream=True)
    pdf = pd.concat(pdfData, ignore_index=True)
    
    # Eliminar filas completamente vacías
    pdf = pdf.dropna(how='all')
    
    # Separar pago y clave
    pdf[["pago", "clave"]] = pdf["Deposito"].astype(str).str.split(".", expand=True)
    
    # Mantener solo columnas relevantes
    cols_a_conservar = ["operación", "pago", "clave"]
    pdf = pdf[cols_a_conservar]

    # -----------------------------
    # LEER EXCEL
    # -----------------------------
    excel = pd.read_excel(archivo_excel, sheet_name="Sheet1")
    excel["mantenimiento_final"] = excel["mantenimiento "].astype(str).str.split(".", expand=True)[0]
    excel = excel.drop(columns=["mantenimiento "])

    # -----------------------------
    # --- INICIO CORRECCIÓN DE TIPOS ---
    # Convertir 'clave' de PDF y Excel a string sin ceros a la izquierda
    pdf["clave"] = pdf["clave"].fillna(0).astype(int).astype(str).str.lstrip("0")
    excel["clave"] = excel["clave"].fillna(0).astype(int).astype(str).str.lstrip("0") 

    # Convertir fechas
    pdf["operación"] = pd.to_datetime(pdf["operación"], errors="coerce", dayfirst=True)
    excel["fecha_limite"] = pd.to_datetime(excel["fecha_limite"], errors="coerce")
    # --- FIN CORRECCIÓN DE TIPOS ---

    # -----------------------------
    # MERGE PDF Y EXCEL
    # -----------------------------
    df_merge = pd.merge(excel, pdf, on="clave", how="left")

    # Agregar mes y año en español
    meses_es = {
        1: "Enero", 2: "Febrero", 3: "Marzo", 4: "Abril",
        5: "Mayo", 6: "Junio", 7: "Julio", 8: "Agosto",
        9: "Septiembre", 10: "Octubre", 11: "Noviembre", 12: "Diciembre"
    }
    df_merge["mes_nombre"] = df_merge["fecha_limite"].dt.month.map(meses_es)
    df_merge["ano_nombre"] = df_merge["fecha_limite"].dt.year

    # -----------------------------
    # FUNCION DE CONCILIACION
    # -----------------------------
    def conciliacion(df):
        for col in ["mantenimiento_final", "pago"]:
            df[col] = df[col].astype(str).str.replace("$", "", regex=False).str.replace(",", "", regex=False).str.strip()
            df[col] = pd.to_numeric(df[col], errors="coerce")
        df["pago"] = df["pago"].fillna(0)

        def calcular_status(row):
            if row["pago"] == 0:
                return "no pagado"
            elif row["operación"] > row["fecha_limite"]:
                return "pago atrasado"
            else:
                return "pagado"

        df["status"] = df.apply(calcular_status, axis=1)
        df["fee"] = df.apply(lambda row: row["mantenimiento_final"] * 0.25 if row["status"] == "pago atrasado" else 0, axis=1)
        df["adeudo"] = df["mantenimiento_final"] + df["fee"] - df["pago"]
        df.loc[(df["status"] == "pagado") & (df["adeudo"] > 0), "status"] = "pago parcial"
        return df

    tabla1 = conciliacion(df_merge)
    tabla1.rename(columns={'operación': 'fecha_operacion'}, inplace=True)

    # -----------------------------
    # GUARDAR EN BASE DE DATOS
    # -----------------------------
    engine = create_engine(f"mysql+pymysql://{usuario}:{password}@{host}:{puerto}/{base_de_datos}")

    # Leer datos existentes
    try:
        existentes = pd.read_sql("SELECT clave, fecha_limite, residencial, pago FROM conciliaciones", con=engine)
        existentes["fecha_limite"] = pd.to_datetime(existentes["fecha_limite"])
        existentes["pago"] = pd.to_numeric(existentes["pago"], errors="coerce")
        existentes["clave"] = existentes["clave"].fillna(0).astype(int).astype(str).str.lstrip("0")
    except Exception as e:
        print(f"No se pudieron leer registros existentes. Se asumirá tabla vacía. Error: {e}")
        existentes = pd.DataFrame(columns=["clave", "fecha_limite", "residencial", "pago"])

    # Convertir tipos de tabla1
    tabla1["fecha_limite"] = pd.to_datetime(tabla1["fecha_limite"])
    tabla1["pago"] = pd.to_numeric(tabla1["pago"], errors="coerce")

    # Filtrar solo registros nuevos
    filtro = tabla1.merge(
        existentes,
        on=["clave", "fecha_limite", "residencial", "pago"],
        how="left",
        indicator=True
    )
    nuevos = filtro[filtro["_merge"] == "left_only"].drop(columns=["_merge"])

    # Insertar solo nuevos
    if not nuevos.empty:
        nuevos.to_sql(name='conciliaciones', con=engine, if_exists='append', index=False)
        print(f"{len(nuevos)} registros nuevos insertados en la base de datos.")
    else:
        print("No hay registros nuevos para insertar.")

    # -----------------------------
    # ELIMINAR REGISTROS INNECESARIOS
    # -----------------------------
    conn = pymysql.connect(host=host, user=usuario, password=password, database=base_de_datos, port=puerto)
    try:
        with conn.cursor() as cursor:
            query1 = """
            DELETE t1 FROM conciliaciones t1
            JOIN (
                SELECT MIN(id_registro) as id_valida, residencial, fecha_limite, clave
                FROM conciliaciones
                WHERE status != 'no pagado'
                GROUP BY residencial, fecha_limite, clave
            ) t2 ON t1.residencial = t2.residencial 
                 AND t1.fecha_limite = t2.fecha_limite 
                 AND t1.clave = t2.clave
            WHERE t1.status = 'no pagado';
            """
            cursor.execute(query1)
            conn.commit()
            print(f"{cursor.rowcount} filas eliminadas (no pagado duplicados con pagados).")
    finally:
        conn.close()

    conn = pymysql.connect(host=host, user=usuario, password=password, database=base_de_datos, port=puerto)
    try:
        with conn.cursor() as cursor:
            query2 = """
            DELETE FROM conciliaciones
            WHERE id_registro NOT IN (
                SELECT id_registro FROM (
                    SELECT MIN(id_registro) AS id_registro
                    FROM conciliaciones
                    WHERE status IN ('pagado', 'no pagado','pago parcial')
                    GROUP BY clave, residencial, fecha_limite, status
                ) AS sub
            )
            AND status IN ('pagado', 'no pagado','pago parcial');
            """
            cursor.execute(query2)
            conn.commit()
            print(f"{cursor.rowcount} filas duplicadas eliminadas.")
    finally:
        conn.close()

    return tabla1


    
