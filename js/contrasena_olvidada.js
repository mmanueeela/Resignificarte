document.getElementById('forgotForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const responseMessage = document.getElementById('responseMessage');
    const submitBtn = document.getElementById('submitBtn');

    responseMessage.textContent = "Enviando solicitud...";
    responseMessage.className = "message";
    submitBtn.disabled = true;

    try {
        const response = await fetch('contrasena_olvidada.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}`
        });

        const data = await response.json();

        if (data.success) {
            responseMessage.textContent = data.message;
            responseMessage.className = "message success";
        } else {
            responseMessage.textContent = data.message;
            responseMessage.className = "message error";
        }
    } catch (error) {
        responseMessage.textContent = "Hubo un error de conexión con el servidor.";
        responseMessage.className = "message error";
    } finally {
        submitBtn.disabled = false;
    }
});