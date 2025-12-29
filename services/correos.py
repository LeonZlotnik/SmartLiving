import smtplib
import pandas as pd
from email.message import EmailMessage

def enviar_correos_pendientes(df, smtp_server, smtp_port, smtp_user, smtp_password, remitente="zlotnik.leon91@gmail.com"):
    
    df_pendientes = df[df['status'].isin(['no pagado', 'pago atrasado'])].copy()
    enviados = 0

    for _, row in df_pendientes.iterrows():
        email_usuario = row.get("email")
        if pd.isna(email_usuario):
            continue
        
        asunto = "Aviso de adeudo de mantenimiento"
        mensaje = f"""
        Estimado(a) residente con del {row['departamento']},

        Le informamos que su pago de mantenimiento correspondiente a la fecha límite {row['fecha_limite'].strftime('%d/%m/%Y')} 
        aún aparece como "{row['status']}". El monto adeudado es de ${row['adeudo']:.2f} MXN.

        Le pedimos realizar el pago a la brevedad para evitar recargos adicionales.

        Gracias por su atención.
        
        Atentamente,
        Administración del Condominio
        """

        msg = EmailMessage()
        msg["Subject"] = asunto
        msg["From"] = remitente
        msg["To"] = email_usuario
        msg.set_content(mensaje)

        try:
            with smtplib.SMTP(smtp_server, smtp_port) as server:
                server.set_debuglevel(1) 
                server.starttls()
                server.login(smtp_user, smtp_password)
                server.send_message(msg)
                enviados += 1
                print(f"Correo enviado a {email_usuario}")
        except Exception as e:
            print(f"Error al enviar a {email_usuario}: {e}")

    print(f"\nTotal de correos enviados: {enviados}")
