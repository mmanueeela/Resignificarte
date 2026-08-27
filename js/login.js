const formLogin = document.getElementById('form-login');
const msjErrorCaja = document.getElementById('mensaje-error');

// ---------------------------------------------------------
// MOSTRAR / OCULTAR CONTRASEÑA
// ---------------------------------------------------------
const toggleIcons = document.querySelectorAll('.toggle-password');

toggleIcons.forEach(icon => {
    icon.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);

        if (!input) return;

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
// VOLVER ATRÁS INTELIGENTE
// ---------------------------------------------------------
function volverAtrasSeguro() {
    // Obtenemos la URL de la página anterior
    let paginaAnterior = document.referrer.toLowerCase();

    // Si venimos de recuperar/restablecer contraseña, o si abrieron la pestaña directamente (sin historial)
    if (paginaAnterior.includes('contrasena_olvidada.php') ||
        paginaAnterior.includes('restablecer_contrasena.php') ||
        paginaAnterior.includes('registro.php') ||
        paginaAnterior === '') {
        window.location.href = 'homepage.php'; // Cortamos el bucle
    } else {
        window.history.back(); // Volvemos a Obras, Contacto, etc.
    }
}

// ---------------------------------------------------------
// MOSTRAR MENSAJE
// ---------------------------------------------------------
function mostrarMensaje(mensaje, tipo) {
    if (!msjErrorCaja) return;

    msjErrorCaja.textContent = mensaje;
    msjErrorCaja.style.display = 'block';

    if (tipo === 'error') {
        msjErrorCaja.style.backgroundColor = 'rgba(255, 77, 77, 0.2)';
        msjErrorCaja.style.border = '1px solid rgba(255, 77, 77, 0.5)';
        msjErrorCaja.style.color = '#fce1e1';
    } else if (tipo === 'exito') {
        msjErrorCaja.style.backgroundColor = 'rgba(77, 255, 145, 0.2)';
        msjErrorCaja.style.border = '1px solid rgba(77, 255, 145, 0.5)';
        msjErrorCaja.style.color = '#e1fce1';
    }
}

// ---------------------------------------------------------
// MOSTRAR MENSAJES ENVIADOS DESDE PHP
// ---------------------------------------------------------
const parametrosURL = new URLSearchParams(window.location.search);
const mensajeErrorURL = parametrosURL.get('error');
const mensajeExitoURL = parametrosURL.get('exito');

// Si PHP manda error
if (mensajeErrorURL) {
    mostrarMensaje(mensajeErrorURL, 'error');
    window.history.replaceState(null, null, window.location.pathname);
}
// Si PHP manda éxito
else if (mensajeExitoURL) {
    mostrarMensaje(mensajeExitoURL, 'exito');
    window.history.replaceState(null, null, window.location.pathname);
}

// ---------------------------------------------------------
// VALIDACIÓN DEL LOGIN
// ---------------------------------------------------------
if (formLogin) {
    formLogin.addEventListener('submit', function(e) {
        let error = '';

        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        const email = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value : '';

        // Campos vacíos
        if (!email || !password) {
            error = "Por favor, rellena todos los campos.";
        }
        // Validar email
        else if (!/^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(email)) {
            error = "Introduce un email válido (ej: usuario@correo.com).";
        }

        // Si hay errores
        if (error !== '') {
            e.preventDefault();
            mostrarMensaje(error, 'error');
            return;
        }

        // Login correcto, dejamos actuar a PHP
        if (msjErrorCaja) {
            msjErrorCaja.style.display = 'none';
        }

        // Evitar doble envío
        const boton = formLogin.querySelector('button[type="submit"]');
        if (boton) {
            boton.disabled = true;
            boton.textContent = "Entrando...";
        }
    });
}