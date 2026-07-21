const formLogin = document.getElementById('form-login');
const msjErrorCaja = document.getElementById('mensaje-error');

// ---------------------------------------------------------
// 1. LEER ERRORES DESDE PHP (Ej: "Contraseña incorrecta")
// ---------------------------------------------------------
const parametrosURL = new URLSearchParams(window.location.search);
const mensajeErrorURL = parametrosURL.get('error');

if (mensajeErrorURL) {
    msjErrorCaja.textContent = mensajeErrorURL;
    msjErrorCaja.style.display = 'block';

    // Limpiamos la URL para que el error no se quede ahí al recargar
    window.history.replaceState(null, null, window.location.pathname);
}

// ---------------------------------------------------------
// 2. VALIDACIÓN INSTANTÁNEA ANTES DE ENVIAR (Frontend)
// ---------------------------------------------------------
if (formLogin) {
    formLogin.addEventListener('submit', function(e) {
        let error = '';

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        // Regla 1: Comprobar que no estén vacíos
        if (!email || !password) {
            error = "Por favor, rellena todos los campos.";
        }
        // Regla 2: Comprobar que el email tiene forma de email
        else if (!/^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(email)) {
            error = "Introduce un email válido (ej: usuario@correo.com).";
        }

        // Si detectamos un fallo, bloqueamos el envío y mostramos el error
        if (error !== '') {
            e.preventDefault(); // Detiene el formulario
            msjErrorCaja.textContent = error;
            msjErrorCaja.style.display = 'block';
        } else {
            // Si todo está perfecto, ocultamos errores y dejamos que siga hacia procesar_login.php
            msjErrorCaja.style.display = 'none';
        }
    });
}