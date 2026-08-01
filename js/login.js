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
// MOSTRAR MENSAJE DE PHP
// ---------------------------------------------------------

const parametrosURL = new URLSearchParams(window.location.search);

const mensajeErrorURL = parametrosURL.get('error');
const mensajeExitoURL = parametrosURL.get('exito');

// Función para mostrar mensaje
function mostrarMensaje(mensaje, tipo) {

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

// Si PHP manda un error
if (mensajeErrorURL) {

    mostrarMensaje(mensajeErrorURL, 'error');

// Limpiar la URL
    window.history.replaceState(
        null,
        null,
        window.location.pathname
    );

}

// Si PHP manda un mensaje de éxito
else if (mensajeExitoURL) {

    mostrarMensaje(mensajeExitoURL, 'exito');

// Limpiar la URL
    window.history.replaceState(
        null,
        null,
        window.location.pathname
    );

}

// ---------------------------------------------------------
// VALIDACIÓN DEL LOGIN
// ---------------------------------------------------------

if (formLogin) {

    formLogin.addEventListener('submit', function(e) {

        let error = '';

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;


        // Comprobar campos vacíos
        if (!email || !password) {

            error = "Por favor, rellena todos los campos.";

        }


        // Comprobar formato del email
        else if (!/^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(email)) {

            error = "Introduce un email válido (ej: usuario@correo.com).";

        }


        // Si hay error
        if (error !== '') {

            e.preventDefault();

            mostrarMensaje(error, 'error');

        }

        // Si todo está correcto, dejamos que PHP procese el login
        else {

            msjErrorCaja.style.display = 'none';

        }

    });

}