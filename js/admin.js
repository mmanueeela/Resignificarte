const btnAddCuadro = document.getElementById('btn-add-cuadro');
const contenedorCuadros = document.getElementById('contenedor-cuadros');

if (btnAddCuadro && contenedorCuadros) {
    let contadorCuadros = 1;

    btnAddCuadro.addEventListener('click', () => {
        contadorCuadros++;

        const nuevoCuadro = document.createElement('div');
        nuevoCuadro.classList.add('bloque-cuadro');
        nuevoCuadro.innerHTML = `
                <div class="cabecera-cuadro">
                    <h3>Cuadro ${contadorCuadros}</h3>
                    <button type="button" class="btn-quitar-cuadro">X Eliminar este cuadro</button>
                </div>
                <div class="inputs-fila">
                    <input type="text" name="titulos[]" placeholder="Título del Cuadro" required class="input-admin">
                    <div class="input-file-custom">
                        <label>📸 Subir Imagen</label>
                        <span class="btn-falso">Seleccionar archivo</span>
                        <input type="file" name="imagenes[]" accept="image/*" required>
                        <span class="nombre-archivo">Ningún archivo seleccionado</span>
                    </div>
                    <div class="input-file-custom">
                        <label>🎵 Subir Audio</label>
                        <span class="btn-falso">Seleccionar archivo</span>
                        <input type="file" name="audios[]" accept="audio/*" required>
                        <span class="nombre-archivo">Ningún archivo seleccionado</span>
                    </div>
                </div>
                <textarea name="transcripciones[]" placeholder="Pega aquí el HTML de la transcripción..." required class="textarea-admin"></textarea>
                
                <label class="checkbox-recompensa">
                    <input type="checkbox" name="es_recompensa[${contadorCuadros - 1}]" value="1"> ¿Es el cuadro secreto de recompensa final?
                </label>
            `;

        contenedorCuadros.appendChild(nuevoCuadro);

        const btnQuitar = nuevoCuadro.querySelector('.btn-quitar-cuadro');
        btnQuitar.addEventListener('click', function() {
            nuevoCuadro.remove();
        });
    });
}

// Delegación de eventos para capturar los cambios en TODOS los inputs file (incluso los nuevos)
document.addEventListener('change', function(e) {
    if (e.target && e.target.type === 'file') {
        const nombreArchivoSpan = e.target.parentElement.querySelector('.nombre-archivo');
        if (nombreArchivoSpan) {
            if (e.target.files.length > 0) {
                nombreArchivoSpan.textContent = e.target.files[0].name;
            } else {
                nombreArchivoSpan.textContent = "Ningún archivo seleccionado";
            }
        }
    }
});