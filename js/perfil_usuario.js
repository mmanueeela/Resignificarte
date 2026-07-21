// 1. Seleccionar automáticamente el país del usuario
const selectPais = document.getElementById('pais');
if (selectPais) {
    // Leemos el valor que PHP dejó preparadito en el atributo del HTML
    const paisGuardado = selectPais.getAttribute('data-pais-guardado');
    if (paisGuardado) {
        selectPais.value = paisGuardado;
    }
}

// 2. Lógica para los botones Editar / Aceptar / Cancelar
const form = document.getElementById('form-perfil');
const btnEdit = document.getElementById('btn-edit');
const btnSave = document.getElementById('btn-save');
const btnCancel = document.getElementById('btn-cancel');
const inputs = form.querySelectorAll('input, select');

// Objeto para guardar los datos originales en caso de cancelar
let datosOriginales = {};

// Al pulsar Editar
if (btnEdit) {
    btnEdit.addEventListener('click', () => {
        inputs.forEach(input => {
            if(input.name !== 'accion') { // No tocamos el campo oculto
                datosOriginales[input.name] = input.value;
                input.disabled = false; // Desbloqueamos los campos
            }
        });
        form.classList.add('modo-edicion'); // Cambiamos el estilo
        btnEdit.style.display = 'none';
        btnSave.style.display = 'inline-block';
        btnCancel.style.display = 'inline-block';
    });
}

// Al pulsar Cancelar
if (btnCancel) {
    btnCancel.addEventListener('click', () => {
        inputs.forEach(input => {
            if(input.name !== 'accion') {
                input.value = datosOriginales[input.name]; // Restauramos lo que había
                input.disabled = true; // Volvemos a bloquear
            }
        });
        form.classList.remove('modo-edicion');
        btnEdit.style.display = 'inline-block';
        btnSave.style.display = 'none';
        btnCancel.style.display = 'none';
    });
}