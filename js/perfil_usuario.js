document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('form-perfil');

    // Si no hay formulario en esta página, detenemos el script
    if (!form) return;

    // 1. Limpiamos cualquier caché residual que el navegador intente restaurar
    form.reset();

    // 2. Variables de elementos
    const btnAccion = document.getElementById('btn-accion-perfil');
    const inputFoto = document.getElementById('input-foto');
    const avatarPreview = document.getElementById('avatar-preview');

    // Seleccionamos todos los campos que queremos habilitar al editar
    // (Excluimos los inputs de tipo hidden que se usan para lógica interna)
    const camposFormulario = form.querySelectorAll('input:not([type="hidden"]), select');

    let enModoEdicion = false;

    // ==========================================================
    // LÓGICA DEL BOTÓN EDITAR / SAVE
    // ==========================================================
    if (btnAccion) {
        btnAccion.addEventListener('click', (e) => {
            if (!enModoEdicion) {
                // --- 1. PASAR A MODO EDICIÓN ---
                e.preventDefault(); // Evitamos que el formulario se envíe por accidente
                enModoEdicion = true;

                // Añadimos la clase para que el CSS muestre las flechas y el icono de foto
                form.classList.add('modo-edicion');

                // Cambiamos el texto y el tipo del botón
                btnAccion.textContent = "Guardar";
                btnAccion.type = "submit"; // Ahora el botón está listo para enviar los datos

                // Habilitamos todos los campos de texto, selects y el input file de la foto
                camposFormulario.forEach(campo => {
                    campo.disabled = false;
                });

            } else {
                // --- 2. GUARDAR DATOS ---
                // Como el botón ya es type="submit", no hacemos e.preventDefault().
                // El navegador se encargará de enviar los datos al archivo PHP.
            }
        });
    }

    // ==========================================================
    // LÓGICA DE SUBIDA DE IMAGEN Y PREVISUALIZACIÓN
    // ==========================================================
    if (inputFoto && avatarPreview) {
        inputFoto.addEventListener('change', function (event) {
            const archivo = event.target.files[0];
            if (!archivo) return;

            // Validación del formato (solo imágenes)
            if (!archivo.type.startsWith('image/')) {
                alert('Por favor, selecciona un archivo de imagen válido.');
                inputFoto.value = "";
                return;
            }

            // Validación del peso (Máximo 2MB)
            if (archivo.size > 2 * 1024 * 1024) {
                alert('La imagen es demasiado grande. El tamaño máximo permitido es de 2MB.');
                inputFoto.value = "";
                return;
            }

            // Previsualización instantánea de la imagen seleccionada
            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(archivo);
        });
    }
});