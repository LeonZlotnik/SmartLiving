import pandas as pd
from sqlalchemy import create_engine
import pymysql

def procesar_egresos(ruta_excel):
    # Cargar archivo de Excel desde la ruta recibida
    egresos = pd.read_excel(ruta_excel, sheet_name="Sheet1")

    # Calcular IVA y total
    egresos["iva"] = egresos["subtotal"] * 0.16
    egresos["total"] = egresos["subtotal"] + egresos["iva"]

    # Agregar mes y año en español
    meses_es = {
    1: "Enero", 2: "Febrero", 3: "Marzo", 4: "Abril",
    5: "Mayo", 6: "Junio", 7: "Julio", 8: "Agosto",
    9: "Septiembre", 10: "Octubre", 11: "Noviembre", 12: "Diciembre"
    }

    egresos["fecha_transaccion"] = pd.to_datetime(egresos["fecha_transaccion"], errors="coerce")
    egresos["mes_nombre"] = egresos["fecha_transaccion"].dt.month.map(meses_es)
    egresos["ano_nombre"] = egresos["fecha_transaccion"].dt.year

    # Parámetros de conexión
    usuario = "root"
    password = "password"
    host = "localhost"
    puerto = 3305
    base_de_datos = "condominios"

    # Crear el engine
    engine = create_engine(f"mysql+pymysql://{usuario}:{password}@{host}:{puerto}/{base_de_datos}")

    # Insertar en la tabla 'egresos'
    egresos.to_sql(name='egresos', con=engine, if_exists='append', index=False)

    return egresos

