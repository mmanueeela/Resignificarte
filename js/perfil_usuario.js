const form = document.getElementById('form-perfil');

// 1. Limpiamos cualquier caché residual que el navegador intente restaurar
if (form) {
    form.reset();
}

// 2. Seleccionar automáticamente el país del usuario
const selectPais = document.getElementById('pais');
if (selectPais) {
    const paisGuardado = selectPais.getAttribute('data-pais-guardado');
    if (paisGuardado) {
        setTimeout(() => {
            selectPais.value = paisGuardado.trim();
        }, 50);
    }
}

// 3. Variables para botones, inputs y avatar
const btnEdit = document.getElementById('btn-edit');
const btnSave = document.getElementById('btn-save');
const btnCancel = document.getElementById('btn-cancel');

// IMPORTANTE:
// El email queda excluido de esta lista.
// Por tanto, aunque pulsemos "Editar Perfil", seguirá estando bloqueado.
const inputs = form
    ? form.querySelectorAll('input:not([name="email"]):not([name="accion"]):not([type="file"]), select')
    : [];

const avatarContainer = document.getElementById('avatar-container');
const inputFoto = document.getElementById('input-foto');
const avatarPreview = document.getElementById('avatar-preview');

// Guardamos la ruta original por si el usuario cancela
let avatarOriginalSrc = '';
if (avatarPreview) {
    avatarOriginalSrc = avatarPreview.src;
}

// Guardamos los datos originales de los campos editables
let datosOriginales = {};

// ==========================================================
// LÓGICA DEL BOTÓN EDITAR
// ==========================================================
if (btnEdit) {
    btnEdit.addEventListener('click', () => {
        // Guardar valores originales y habilitar campos editables
        inputs.forEach(input => {
            datosOriginales[input.name] = input.value;
            input.disabled = false;
        });

        // Cambiar botones
        form.classList.add('modo-edicion');
        btnEdit.style.display = 'none';
        btnSave.style.display = 'inline-block';
        btnCancel.style.display = 'inline-block';

        // Activar edición del avatar
        if (avatarContainer) {
            avatarContainer.classList.add('modo-edicion');
            avatarContainer.title = "Haz clic para cambiar tu foto";
        }
    });
}

// ==========================================================
// LÓGICA DEL BOTÓN CANCELAR
// ==========================================================
if (btnCancel) {
    btnCancel.addEventListener('click', () => {
        // Restaurar valores originales
        inputs.forEach(input => {
            if (datosOriginales[input.name] !== undefined) {
                input.value = datosOriginales[input.name];
            }
            input.disabled = true;
        });

        // Cambiar botones
        form.classList.remove('modo-edicion');
        btnEdit.style.display = 'inline-block';
        btnSave.style.display = 'none';
        btnCancel.style.display = 'none';

        // Restaurar avatar
        if (avatarContainer && avatarPreview && inputFoto) {
            avatarContainer.classList.remove('modo-edicion');
            avatarContainer.title = "Haz clic en Editar Perfil para cambiar tu foto";
            avatarPreview.src = avatarOriginalSrc;
            inputFoto.value = "";
        }
    });
}

// ==========================================================
// LÓGICA DE SUBIDA DE IMAGEN
// ==========================================================
if (avatarContainer && inputFoto && avatarPreview) {
    // Al hacer clic en el avatar, si estamos editando, abrimos el selector
    avatarContainer.addEventListener('click', () => {
        if (avatarContainer.classList.contains('modo-edicion')) {
            inputFoto.click();
        }
    });

    // Cuando el usuario elige un archivo
    inputFoto.addEventListener('change', function (event) {
        const archivo = event.target.files[0];
        if (!archivo) {
            return;
        }

        // ==========================================
        // VALIDACIÓN DEL FORMATO
        // ==========================================
        if (!archivo.type.startsWith('image/')) {
            alert('Por favor, selecciona un archivo de imagen válido.');
            inputFoto.value = "";
            return;
        }

        // ==========================================
        // VALIDACIÓN DEL PESO
        // ==========================================
        if (archivo.size > 2 * 1024 * 1024) {
            alert('La imagen es demasiado grande. Máximo 2MB.');
            inputFoto.value = "";
            return;
        }

        // ==========================================
        // PREVISUALIZACIÓN
        // ==========================================
        const reader = new FileReader();
        reader.onload = function (e) {
            avatarPreview.src = e.target.result;
        };
        reader.readAsDataURL(archivo);
    });
}