const formLogin = document.getElementById('form-login');
const msjErrorCaja = document.getElementById('mensaje-error');

// ---------------------------------------------------------
// 0. LÓGICA PARA MOSTRAR/OCULTAR CONTRASEÑA (EL OJITO)
// ---------------------------------------------------------
const toggleIcons = document.querySelectorAll('.toggle-password');

toggleIcons.forEach(icon => {
    icon.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);

        if (input.type === 'password') {
            input.type = 'text';
            this.src = 'src/iconos/ojo-abierto.png';
        } else {
            input.type = 'password';
            this.src = 'src/iconos/ojo-cerrado.png';
        }
    });
});

// ---------------------------------------------------------
// 1. LEER MENSAJES DESDE PHP (Errores o Éxito)
// ---------------------------------------------------------
const parametrosURL = new URLSearchParams(window.location.search);
const mensajeErrorURL = parametrosURL.get('error');
const mensajeExitoURL = parametrosURL.get('exito');

if (mensajeErrorURL) {
    msjErrorCaja.textContent = mensajeErrorURL;
    msjErrorCaja.style.display = 'block';
    msjErrorCaja.style.backgroundColor = 'rgba(255, 77, 77, 0.2)';
    msjErrorCaja.style.border = '1px solid rgba(255, 77, 77, 0.5)';
    msjErrorCaja.style.color = '#fce1e1';
    window.history.replaceState(null, null, window.location.pathname);
} else if (mensajeExitoURL) {
    msjErrorCaja.textContent = mensajeExitoURL;
    msjErrorCaja.style.display = 'block';
    // Estilos dinámicos en verde translúcido para el éxito
    msjErrorCaja.style.backgroundColor = 'rgba(77, 255, 145, 0.2)';
    msjErrorCaja.style.border = '1px solid rgba(77, 255, 145, 0.5)';
    msjErrorCaja.style.color = '#e1fce1';
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

        // Si detectamos un fallo, bloqueamos el envío y mostramos el error en rojo
        if (error !== '') {
            e.preventDefault();
            msjErrorCaja.textContent = error;
            msjErrorCaja.style.display = 'block';
            msjErrorCaja.style.backgroundColor = 'rgba(255, 77, 77, 0.2)';
            msjErrorCaja.style.border = '1px solid rgba(255, 77, 77, 0.5)';
            msjErrorCaja.style.color = '#fce1e1';
        } else {
            msjErrorCaja.style.display = 'none';
        }
    });
}