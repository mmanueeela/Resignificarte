const formContacto = document.getElementById('form-contacto');
const msjErrorCaja = document.getElementById('mensaje-error');

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

    // Traducir los errores de PHP a mensajes legibles
    let textoError = "Ha ocurrido un error.";
    if(mensajeErrorURL === "campos") textoError = "Por favor, rellena todos los campos.";
    if(mensajeErrorURL === "email") textoError = "Error al enviar el email. Inténtalo más tarde.";

    mostrarMensaje(textoError, 'error');
    window.history.replaceState(null, null, window.location.pathname);
}
// Si PHP manda éxito
else if (mensajeExitoURL) {
    mostrarMensaje("Mensaje enviado correctamente.", 'exito');
    window.history.replaceState(null, null, window.location.pathname);
}

// ---------------------------------------------------------
// VALIDACIÓN DEL FORMULARIO ANTES DE ENVIAR
// ---------------------------------------------------------
if (formContacto) {
    formContacto.addEventListener('submit', function(e) {
        let error = '';

        const nombreInput = document.getElementById('nombre');
        const emailInput = document.getElementById('email');
        const asuntoInput = document.getElementById('asunto');
        const mensajeInput = document.getElementById('mensaje');

        const nombre = nombreInput ? nombreInput.value.trim() : '';
        const email = emailInput ? emailInput.value.trim() : '';
        const asunto = asuntoInput ? asuntoInput.value.trim() : '';
        const contenidoMensaje = mensajeInput ? mensajeInput.value.trim() : '';

        // Validar campos vacíos
        if (!nombre || !email || !asunto || !contenidoMensaje) {
            error = "Por favor, rellena todos los campos.";
        }
        // Validar email
        else if (!/^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(email)) {
            error = "Introduce un email válido (ej: usuario@correo.com).";
        }

        // Si hay errores, detener el envío
        if (error !== '') {
            e.preventDefault();
            mostrarMensaje(error, 'error');
            return;
        }

        // Si todo es correcto, dejamos que se envíe y cambiamos el botón
        if (msjErrorCaja) {
            msjErrorCaja.style.display = 'none';
        }

        const boton = formContacto.querySelector('button[type="submit"]');
        if (boton) {
            boton.disabled = true;
            boton.textContent = "ENVIANDO...";
        }
    });
}