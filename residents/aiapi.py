import os
import mysql.connector
from dotenv import load_dotenv
from openai import OpenAI

from langchain.docstore.document import Document
from langchain_openai import OpenAIEmbeddings
from langchain_chroma import Chroma
import shutil

load_dotenv()

# Configura la API key
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
if not OPENAI_API_KEY:
    raise RuntimeError("Falta OPENAI_API_KEY en el entorno / .env")

client = OpenAI(api_key=OPENAI_API_KEY)

# === EMBEDDINGS GLOBAL ===
embeddings = OpenAIEmbeddings(openai_api_key=OPENAI_API_KEY)

# === CONFIG DB MySQL ===
def get_mysql_connection():
    return mysql.connector.connect(
        user="root",
        password="password",
        host="localhost",
        port=3305,
        database="condominios"
    )

# === GENERAR EMBEDDINGS DESDE MYSQL PARA UN USUARIO ===
def build_index(user_id):
    try:
        # borrar índice viejo
        shutil.rmtree("chroma_index", ignore_errors=True)

        conn = get_mysql_connection()
        cursor = conn.cursor(dictionary=True)

        docs = []

        # Obtener egresos (globales)
        cursor.execute("""
            SELECT concepto, beneficiario, subtotal, iva, total, fecha_transaccion, procedencia, fecha_registrada, mes_nombre, ano_nombre 
            FROM egresos
        """)
        egresos = cursor.fetchall()

        for row in egresos:
            texto = (
                f"Egreso registrado bajo el concepto {row['concepto']} para el beneficiario {row['beneficiario']}, "
                f"subtotal ${row['subtotal']}, IVA ${row['iva']}, total ${row['total']}. "
                f"Pago realizado el {row['fecha_transaccion']} y registrado el {row['fecha_registrada']}. "
                f"Corresponde al mes de {row['mes_nombre']} del {row['ano_nombre']}. "
                f"El presupuesto fue pagado desde {row['procedencia']}."
            )
            docs.append(Document(page_content=texto, metadata={"user_id": "global", "tipo": "egreso"}))

        # Obtener pagos filtrados por usuario
        cursor.execute("""
            SELECT fecha_operacion, pago, residencial, departamento, fecha_limite, 
                   mantenimiento_final, status, fee, adeudo, fecha_conciliacion, email, mes_nombre, ano_nombre 
            FROM conciliaciones WHERE email = %s
        """, (user_id,))
        pagos = cursor.fetchall()

        for row in pagos:
            texto = (
                f"El condómino {row['email']} pagó ${row['pago']} el {row['fecha_operacion']}. "
                f"Es residente del residencial {row['residencial']}, departamento {row['departamento']}, "
                f"con un mantenimiento mensual de ${row['mantenimiento_final']}. "
                f"Este pago corresponde al mes de {row['mes_nombre']} del {row['ano_nombre']}. "
                f"El status actual es {row['status']}, con un adeudo de ${row['adeudo']} y un fee de ${row['fee']}. "
                f"La fecha límite de pago fue {row['fecha_limite']}. "
                f"La última conciliación fue registrada el {row['fecha_conciliacion']}."
            )
            docs.append(Document(page_content=texto, metadata={"user_id": user_id, "tipo": "pago"}))

        conn.close()

        if not docs:
            print("No se encontraron datos para el usuario.")
            return None

        # Crear índice persistente con Chroma
        db = Chroma.from_documents(
            documents=docs,
            embedding=embeddings,
            persist_directory="chroma_index"
        )
        return db

    except Exception as e:
        print(f"Error creando el índice: {e}")
        return None

# === RESPUESTA DEL CHAT FILTRADA POR USUARIO ===
def generateChatResponse(prompt, user_id):
    try:
        db = Chroma(
            persist_directory="chroma_index",
            embedding_function=embeddings
        )

        # Buscar docs relevantes
        results_raw = db.similarity_search(prompt, k=10)

        # Filtrar solo global o del user_id actual
        results = [
            doc for doc in results_raw
            if doc.metadata.get("user_id") == user_id or doc.metadata.get("user_id") == "global"
        ]

        if not results:
            return "No se encontraron datos relevantes para tu usuario."

        # Contexto para el modelo
        context = "\n".join([doc.page_content for doc in results[:3]])

        messages = [
            {"role": "system", "content": "Responde SOLO usando la siguiente información. Si no está en el contexto, responde 'No tengo datos sobre eso'."},
            {"role": "system", "content": f"Información relevante:\n{context}"},
            {"role": "user", "content": prompt}
        ]

        contextual_response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=messages
        )

        return contextual_response.choices[0].message.content

    except Exception as e:
        return f"Error al cargar el índice: {str(e)}"





