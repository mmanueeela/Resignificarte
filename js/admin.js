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
                        <input type="file" name="imagenes[]" accept="image/*" required>
                    </div>
                    <div class="input-file-custom">
                        <label>🎵 Subir Audio</label>
                        <input type="file" name="audios[]" accept="audio/*" required>
                    </div>
                </div>
                <textarea name="transcripciones[]" placeholder="Pega aquí el HTML de la transcripción..." required class="textarea-admin"></textarea>
                
                <label class="checkbox-recompensa">
                    <!-- Usamos el contador - 1 para el array de recompensas en PHP -->
                    <input type="checkbox" name="es_recompensa[${contadorCuadros - 1}]" value="1"> ¿Es el cuadro secreto de recompensa final?
                </label>
            `;

        contenedorCuadros.appendChild(nuevoCuadro);

        // Funcionalidad para eliminar el cuadro añadido si se equivoca
        const btnQuitar = nuevoCuadro.querySelector('.btn-quitar-cuadro');
        btnQuitar.addEventListener('click', function() {
            nuevoCuadro.remove();
        });
    });
}