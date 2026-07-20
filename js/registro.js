// Seleccionamos todos los ojitos de la página
const togglePasswords = document.querySelectorAll('.toggle-password');

togglePasswords.forEach(ojo => {
    ojo.addEventListener('click', function() {
        // Buscamos el input que corresponde a este ojito concreto
        const inputId = this.getAttribute('data-target');
        const input = document.getElementById(inputId);

        // Si es tipo contraseña, lo cambiamos a texto. Si es texto, a contraseña.
        if (input.type === 'password') {
            input.type = 'text';
            // Cambia esta ruta a la de tu icono de ojo abierto
            this.src = 'src/iconos/ojo-abierto.png';
        } else {
            input.type = 'password';
            // Cambia esta ruta a la de tu icono de ojo cerrado
            this.src = 'src/iconos/ojo-cerrado.png';
        }
    });
});

// Rellenar los días automáticamente
const selectDia = document.getElementById('dia');
for(let i=1; i<=31; i++) {
    selectDia.innerHTML += `<option value="${i}">${i}</option>`;
}

const selectAnyo = document.getElementById('anyo');
const currentYear = new Date().getFullYear();
for(let i=currentYear; i>=1920; i--) {
    selectAnyo.innerHTML += `<option value="${i}">${i}</option>`;
}

// --- LÓGICA DEL POPUP DE TÉRMINOS Y CONDICIONES ---
const contenedorTerminos = document.getElementById('abrir-popup');
const checkboxTerminos = document.getElementById('accept-terms');
const popupOverlay = document.getElementById('popup-overlay');
const btnCerrarPopup = document.getElementById('cerrar-popup');
const btnAceptarPopup = document.getElementById('btn-aceptar-popup');

// 1. Al hacer clic en la línea del checkbox, evitamos que se marque y abrimos el popup
contenedorTerminos.addEventListener('click', (e) => {
    e.preventDefault(); // Impide el comportamiento por defecto del HTML

    // Si ya está marcado (true), simplemente lo desmarcamos sin abrir nada
    if (checkboxTerminos.checked) {
        checkboxTerminos.checked = false;
    }
    // Si no está marcado (false), abrimos el popup para obligarle a leer/aceptar
    else {
        popupOverlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
});

// 2. Cerrar popup desde la 'X' (no marca el checkbox)
btnCerrarPopup.addEventListener('click', () => {
    popupOverlay.style.display = 'none';
    document.body.style.overflow = '';
});

// 3. Cerrar popup haciendo clic fuera de la caja morada (no marca el checkbox)
popupOverlay.addEventListener('click', (e) => {
    if (e.target === popupOverlay) {
        popupOverlay.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// 4. ¡LA ÚNICA FORMA DE ACEPTAR!
btnAceptarPopup.addEventListener('click', () => {
    checkboxTerminos.checked = true; // Activamos el check por código
    popupOverlay.style.display = 'none'; // Cerramos el popup
    document.body.style.overflow = '';
});