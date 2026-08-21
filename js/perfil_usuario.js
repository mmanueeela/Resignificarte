document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('form-perfil');
    if (!form) return;

    form.reset();

    const btnEditar = document.getElementById('btn-editar-perfil');
    const btnCancelar = document.getElementById('btn-cancelar');
    const inputFoto = document.getElementById('input-foto');
    const avatarPreview = document.getElementById('avatar-preview');

    // CLAVE: Excluimos el input name="email" para que nunca se habilite
    const camposFormulario = form.querySelectorAll('input:not([type="hidden"]):not([name="email"]), select');

    let enModoEdicion = false;
    let datosOriginales = {};
    let fotoOriginalSrc = avatarPreview.src;

    if (btnEditar) {
        btnEditar.addEventListener('click', (e) => {
            e.preventDefault();
            if (enModoEdicion) return;

            enModoEdicion = true;
            form.classList.add('modo-edicion');

            btnEditar.disabled = true;
            btnEditar.style.opacity = '0.4';
            btnEditar.style.cursor = 'default';

            camposFormulario.forEach(campo => {
                datosOriginales[campo.name] = campo.value;
                campo.disabled = false;
            });
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', (e) => {
            e.preventDefault();

            enModoEdicion = false;
            form.classList.remove('modo-edicion');

            btnEditar.disabled = false;
            btnEditar.style.opacity = '1';
            btnEditar.style.cursor = 'pointer';

            camposFormulario.forEach(campo => {
                if (datosOriginales[campo.name] !== undefined) {
                    campo.value = datosOriginales[campo.name];
                }
                campo.disabled = true;
            });

            avatarPreview.src = fotoOriginalSrc;
            inputFoto.value = "";
        });
    }

    if (inputFoto && avatarPreview) {
        inputFoto.addEventListener('change', function (event) {
            const archivo = event.target.files[0];
            if (!archivo) return;

            if (!archivo.type.startsWith('image/')) {
                alert('Por favor, selecciona un archivo de imagen válido.');
                inputFoto.value = "";
                return;
            }

            if (archivo.size > 2 * 1024 * 1024) {
                alert('La imagen es demasiado grande. El tamaño máximo permitido es de 2MB.');
                inputFoto.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(archivo);
        });
    }
});