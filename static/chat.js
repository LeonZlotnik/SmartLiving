console.log("CHAT.JS SE EJECUTÓ");

const socket = io({
    withCredentials: true
});

// Selección de elementos
const messageInput = document.getElementById("message");

// -----------------------
//  ENVIAR MENSAJE
// -----------------------

messageInput.addEventListener("keypress", e => {
    if (e.key === "Enter") sendMessage();
});

function sendMessage() {
    const text = messageInput.value.trim();
    if (!text) return;

    if (!USERNAME) {
        console.warn("USERNAME no está definido todavía");
        return;
    }

    console.log("ENVIANDO MENSAJE:", text);

    socket.emit("send_message", {
        user: USERNAME,
        receiver: RECEIVER || "support",
        message: text
    });

    messageInput.value = "";
}

window.sendMessage = sendMessage;

// -----------------------
//  SOCKET.IO - ON CONNECT
// -----------------------

socket.on("connect", () => {
    console.log("Socket conectado:", socket.id);

    if (USERNAME) {
        console.log("Registrando usuario:", USERNAME, "=>", RECEIVER);
        socket.emit("register_user", {
            username: USERNAME,
            receiver: RECEIVER,
        });
    }
});

// -----------------------
//  RECIBIR MENSAJES LIVE
// -----------------------

socket.on("receive_message", (data) => {
    const isMe = data.user === USERNAME;
    const cls = isMe ? "msg me" : "msg other";

    chatBox.insertAdjacentHTML("beforeend", `
        <div class="${cls}">
            <strong>${data.user}:</strong> ${data.message}
        </div>
    `);

    chatBox.scrollTop = chatBox.scrollHeight;
});

// -----------------------
//  HISTORIAL
// -----------------------

socket.on("chat_history", (mensajes) => {
    if (!Array.isArray(mensajes)) return;

    // Ordenar por fecha ascendente si viene con timestamp
    mensajes.sort((a, b) =>
        (new Date(a.timestamp || 0)) - (new Date(b.timestamp || 0))
    );

    mensajes.forEach(msg => {
        const isMe = msg.sender === USERNAME;
        const cls = isMe ? "msg me" : "msg other";

        chatBox.insertAdjacentHTML("beforeend", `
            <div class="${cls}">
                <strong>${msg.sender}:</strong> ${msg.message}
            </div>
        `);
    });

    chatBox.scrollTop = chatBox.scrollHeight;
});



