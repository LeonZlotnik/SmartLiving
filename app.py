from flask import Flask, request, session, redirect, url_for, render_template, jsonify
from services.procesador import procesar_archivos
from services.correos import enviar_correos_pendientes
from services.egresos import procesar_egresos
from residents import aiapi
from residents import config
from flask_socketio import SocketIO, emit
from flask_cors import CORS
import os
import mysql.connector
import hmac
import hashlib

SECRET_KEY = b'ZL@$nik199!'

app = Flask(__name__)
CORS(app)
socketio = SocketIO(app, cors_allowed_origins="*")
app.config.from_object(config.config['development'])
app.secret_key = os.environ.get('FLASK_SECRET_KEY', os.urandom(24))

# ---------------------------------------
# Funcion de uso de Token para mensajeria
# ---------------------------------------

def verificar_token(email, token):
    esperado = hmac.new(
        SECRET_KEY,           
        email.encode(),
        hashlib.sha256
    ).hexdigest()

    return hmac.compare_digest(esperado, token)

# ---------------------------------------
# CONEXIÓN A MYSQL
# ---------------------------------------

def get_mysql_connection():
    return mysql.connector.connect(
        user="root",
        password="password",
        host="localhost",
        port=3305,
        database="condominios"
    )

# ---------------------------------------
# FUNCIONES DE MENSAJERÍA (SQL)
# ---------------------------------------

def guardar_mensaje(sender, receiver, message,):
    conn = get_mysql_connection()
    cursor = conn.cursor()

    query = """
        INSERT INTO mensajes (sender, receiver, message, timestamp)
        VALUES (%s, %s, %s, NOW())
    """

    cursor.execute(query,(sender, receiver, message,))
    conn.commit()
    cursor.close()
    conn.close()

def obtener_historial(username):
    conn = get_mysql_connection()
    cursor = conn.cursor(dictionary=True)

    query = """
        SELECT sender, receiver, message, timestamp
        FROM mensajes
        WHERE sender = %s OR receiver = %s
        ORDER BY timestamp ASC
    """
    cursor.execute(query,(username, username))
    msg = cursor.fetchall()

    # Convertir datetime → string
    historial = []
    for row in msg:
        row["timestamp"] = row["timestamp"].strftime("%Y-%m-%d %H:%M:%S")
        historial.append(row)

    cursor.close()
    conn.close()
    return historial

# Carpeta de subida
UPLOAD_FOLDER = 'uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# -----------------------------
# -----------------------------
# RUTA PRINCIPAL
# -----------------------------
# -----------------------------

@app.route("/")
def home():
    return redirect(url_for('login'))

# -----------------------------
# RUTA LOGIN USUARIOS
# -----------------------------

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        email = request.form.get('email')
        password = request.form.get('password')

        if not email or not password:
            return "Faltan credenciales", 400

        conn = get_mysql_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM usuarios WHERE email = %s", (email,))
        user = cursor.fetchone()
        cursor.close()
        conn.close()

        if user:
            if user['password'] == password: 
                session['user_id'] = user['user_id']  # ID interno
                session['email'] = user['email']      # Guardamos email para mostrar
                return redirect(url_for('residents'))
            else:
                return "Contraseña incorrecta", 401
        else:
            return "Usuario no encontrado", 404

    return render_template('login.html')

# -----------------------------
# RUTA PRINCIPAL - CHATBOT
# -----------------------------
@app.route('/residents', methods=['GET', 'POST'])
def residents():
    if 'user_id' not in session:
        return redirect(url_for('login'))

    user_email = session.get('email', '')

    if request.method == 'POST':
        prompt = request.form.get('prompt', '').strip()
        if not prompt:
            return jsonify({'error': 'Prompt vacío'}), 400
        
        # Construir o actualizar el índice para este usuario
        aiapi.build_index(user_email)

        # Generar respuesta usando prompt y email de usuario
        answer = aiapi.generateChatResponse(prompt, user_email)
        return jsonify({'answer': answer}), 200

    return render_template('index.html', user_email=user_email)

# -----------------------------
# RUTA PRINCIPAL - INFO USUARIOS
# -----------------------------

@app.route('/status', methods=['GET', 'POST'])
def status():
     # Asegurarte de que haya sesión activa
    if "email" not in session:
        return redirect(url_for("login"))

    return render_template("cuentas_usuario.html", user_email=session["email"])

# -----------------------------
# RUTA PRINCIPAL - FILTRO USUARIOS
# -----------------------------

@app.route('/filtrar', methods=['POST'])
def filtrar():

    user_email = session.get('email')
    if not user_email:
        return jsonify({"error": "Sesión no válida o usuario no autenticado"}), 401

    residencial = request.form.get("residencial", "").strip()
    departamento = request.form.get("departamento", "").strip()
    status = request.form.get("status", "").strip()
    mes = request.form.get("mes_nombre", "").strip()
    ano = request.form.get("ano_nombre", "").strip()


    # Conectar base de datos
    conn = get_mysql_connection()
    cursor = conn.cursor(dictionary=True)

    # Construcción base del query
    sql = "SELECT * FROM conciliaciones WHERE email = %s"
    params = [user_email] 

    if residencial:
        sql += " AND residencial = %s"
        params.append(residencial)
    if departamento:
        sql += " AND departamento = %s"
        params.append(departamento)
    if status:
        sql += " AND status = %s"
        params.append(status)
    if mes:
        sql += " AND mes_nombre = %s"
        params.append(mes)
    if ano:
        sql += " AND ano_nombre = %s"
        params.append(ano)

    cursor.execute(sql, params)
    results = cursor.fetchall()

    total_pagado = sum(float(r.get("pago", 0) or 0) for r in results)
    total_adeudo = sum(float(r.get("adeudo", 0) or 0) for r in results)

    cursor.close()
    conn.close()

    # Si no hay resultados, renderiza un mensaje
    if not results:
        return render_template(
            "partials/filtro_usuarios.html",
            resultados=[],
            total_pagado=0,
            total_adeudo=0,
            mensaje="No se encontraron resultados."
        )

    # Renderiza la tabla parcial
    return render_template(
        "partials/filtro_usuarios.html",
        resultados=results,
        total_pagado=total_pagado,
        total_adeudo=total_adeudo,
        mensaje=None
    )

# -----------------------------
# RUTA PRINCIPAL - INFO EGRESOS
# -----------------------------

@app.route('/egresos', methods=['GET', 'POST'])
def egresos():
     # Asegurarte de que haya sesión activa
    if "email" not in session:
        return redirect(url_for("login"))

    return render_template("cuentas_egresos.html", user_email=session["email"])

# -----------------------------
# RUTA PRINCIPAL - FILTRO EGRESOS
# -----------------------------

@app.route('/filtro_egresos', methods=['POST'])
def filtro_egresos():

    user_email = session.get('email')
    if not user_email:
        return jsonify({"error": "Sesión no válida o usuario no autenticado"}), 401

    concepto = request.form.get("concepto", "").strip()
    beneficiario = request.form.get("beneficiario", "").strip()
    subtotal = request.form.get("subtotal", "").strip()
    iva = request.form.get("iva", "").strip()
    fecha_transaccion = request.form.get("fecha_transaccion", "").strip()
    total = request.form.get("total", "").strip()
    mes_nombre = request.form.get("mes_nombre", "").strip()
    ano_nombre = request.form.get("ano_nombre", "").strip()


    # Conectar base de datos
    conn = get_mysql_connection()
    cursor = conn.cursor(dictionary=True)

    # Construcción base del query
    sql = "SELECT * FROM egresos"

    if concepto:
        sql += " AND concepto"
    if beneficiario:
        sql += " AND beneficiario"
    if subtotal:
        sql += " AND subtotal"
    if total:
        sql += " AND total"
    if iva:
        sql += " AND iva"
    if fecha_transaccion:
        sql += " AND fecha_transaccion"
    if mes_nombre:
        sql += " AND mes_nombre"
    if ano_nombre:
        sql += " AND ano_nombre"

    cursor.execute(sql)
    results = cursor.fetchall()

    total_egresos = sum(float(r.get("total", 0) or 0) for r in results)

    cursor.close()
    conn.close()

    # Si no hay resultados, renderiza un mensaje
    if not results:
        return render_template(
            "partials/filtro_egresos.html",
            resultados=[],
            total_egresos=0,
            mensaje="No se encontraron resultados."
        )

    # Renderiza la tabla parcial
    return render_template(
        "partials/filtro_egresos.html",
        resultados=results,
        total_egresos=total_egresos,
        mensaje=None
    )

# -----------------------------
# RUTA PARA SERVICIOS DE ARCHIVOS
# -----------------------------
@app.route("/services", methods=["GET", "POST"])
def upload_files():
    if request.method == "POST":

        token = request.form.get("token")
        user = request.form.get("user")

        if not token or not user:
            return "Acceso no autorizado", 403

        # Generamos el token esperado
        expected_token = hmac.new(SECRET_KEY, user.encode(), hashlib.sha256).hexdigest()
        if token != expected_token:
            return "Token inválido", 403

        action_type = request.form.get("action_type")

        if action_type == "conciliacion":
            if "pdf" not in request.files or "excel" not in request.files:
                return "Archivos faltantes", 400

            pdf = request.files["pdf"]
            excel = request.files["excel"]

            pdf_path = os.path.join(app.config["UPLOAD_FOLDER"], "Cuentas.pdf")
            excel_path = os.path.join(app.config["UPLOAD_FOLDER"], "tabla_pagos.xlsx")

            pdf.save(pdf_path)
            excel.save(excel_path)

            try:
                df = procesar_archivos(pdf_path, excel_path)

                enviar_correos_pendientes(
                    df=df,
                    smtp_server="smtp.gmail.com",
                    smtp_port=587,
                    smtp_user=os.getenv("SMTP_USER"),
                    smtp_password=os.getenv("SMTP_PASSWORD"),
                    remitente="administracion@condominio.com"
                )

            except Exception as e:
                print(f"Error: {e}")
                return "Error al procesar conciliación", 500

        elif action_type == "egresos":
            if "excel" not in request.files:
                return "Archivo faltante", 400

            excel_egresos = request.files["excel"]
            egresos_path = os.path.join(app.config["UPLOAD_FOLDER"], "Egresos.xlsx")
            excel_egresos.save(egresos_path)

            try:
                procesar_egresos(egresos_path)
            except Exception as e:
                print(f"Error: {e}")
                return "Error al procesar egresos", 500

    return render_template("success.html")

# -----------------------------
# SISTEMA DE MENSAJERIA 
# -----------------------------

@app.route('/mensajeria', methods=['GET'])
def mensajeria():
    if 'email' not in session:
        return redirect(url_for('login'))

    user_email = session['email']
    user_id = session['user_id']

    return render_template('chat.html', username=user_email, user_id=user_id, receiver="admin")  

@app.route('/msn-admin')
def mensajeria_admin():
    admin_email = request.args.get("admin_email")  
    token = request.args.get("token")
    email = request.args.get("email") 

    if not admin_email or not token or not email:
        return "Faltan parámetros", 400

    expected = hmac.new(
        SECRET_KEY,
        admin_email.encode(),
        hashlib.sha256
    ).hexdigest()

    if not hmac.compare_digest(token, expected):
        return "Token inválido", 403

    return render_template(
        "chat.html", username=admin_email, user_id=admin_email, receiver=email)

@socketio.on('send_message')
def handle_send_message(data):
    print("EVENTO RECIBIDO:", data)
    sender = data.get("user")
    receiver = data.get("receiver")
    message = data.get("message")

    print("Guardar en BDD: ", sender, receiver, message)

    guardar_mensaje(sender, receiver, message)

    emit('receive_message', {
        "user": sender,
        "receiver": receiver,
        "message": message
    }, broadcast=True)

@socketio.on("register_user")
def register_user(username):
    historial = obtener_historial(username)
    emit("chat_history", historial)

# -----------------------------
# MANEJO DE ERRORES
# -----------------------------
@app.errorhandler(404)
def page_not_found(e):
    return render_template("404.html"), 404

# -----------------------------
# INICIO
# -----------------------------
if __name__ == "__main__":
    socketio.run(app, host="0.0.0.0", port=8888, debug=True)
