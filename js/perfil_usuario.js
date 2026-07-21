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
        // Un micro-retraso para asegurarnos de que el JS machaca la memoria del navegador
        setTimeout(() => {
            selectPais.value = paisGuardado.trim();
        }, 50);
    }
}

// 3. Lógica para los botones Editar / Aceptar / Cancelar
const btnEdit = document.getElementById('btn-edit');
const btnSave = document.getElementById('btn-save');
const btnCancel = document.getElementById('btn-cancel');
const inputs = form.querySelectorAll('input, select');

let datosOriginales = {};

if (btnEdit) {
    btnEdit.addEventListener('click', () => {
        inputs.forEach(input => {
            if(input.name !== 'accion') {
                datosOriginales[input.name] = input.value;
                input.disabled = false;
            }
        });
        form.classList.add('modo-edicion');
        btnEdit.style.display = 'none';
        btnSave.style.display = 'inline-block';
        btnCancel.style.display = 'inline-block';
    });
}

if (btnCancel) {
    btnCancel.addEventListener('click', () => {
        inputs.forEach(input => {
            if(input.name !== 'accion') {
                input.value = datosOriginales[input.name];
                input.disabled = true;
            }
        });
        form.classList.remove('modo-edicion');
        btnEdit.style.display = 'inline-block';
        btnSave.style.display = 'none';
        btnCancel.style.display = 'none';
    });
}