// 1. REPRODUCCIÓN AUDIO Y TRANSCRIPCIÓN (Se mantiene igual que antes)
const botonesAudio = document.querySelectorAll('.btn-play-pause');
botonesAudio.forEach(boton => {
    boton.addEventListener('click', function() {
        const audioId = this.getAttribute('data-audio-id');
        const audio = document.getElementById(audioId);
        const iconoPlay = this.querySelector('.icono-play');
        const iconoPause = this.querySelector('.icono-pause');

        if (audio.paused) {
            document.querySelectorAll('audio').forEach(a => {
                a.pause();
                const container = a.parentElement;
                container.querySelector('.icono-play').style.display = 'block';
                container.querySelector('.icono-pause').style.display = 'none';
            });
            audio.play();
            iconoPlay.style.display = 'none';
            iconoPause.style.display = 'block';
        } else {
            audio.pause();
            iconoPlay.style.display = 'block';
            iconoPause.style.display = 'none';
        }
    });
});

const botonesTranscripcion = document.querySelectorAll('.btn-toggle-transcripcion');
botonesTranscripcion.forEach(boton => {
    boton.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        document.getElementById(targetId).classList.toggle('visible');
        this.classList.toggle('abierto');
    });
});

// 2. DESPLEGAR COMENTARIOS (Solo si no están bloqueados)
const botonesComentarios = document.querySelectorAll('.btn-desplegar-comentarios');
botonesComentarios.forEach(boton => {
    boton.addEventListener('click', function() {
        if (!this.classList.contains('bloqueado')) {
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.toggle('abierto');
        }
    });
});

// 3. ENVIAR COMENTARIO REAL POR AJAX
const formulariosComentario = document.querySelectorAll('.form-comentario');
formulariosComentario.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const input = this.querySelector('.input-comentario');
        const comentario = input.value.trim();
        if (comentario === '') return;

        const cuadroId = this.getAttribute('data-cuadro-id');

        // Petición AJAX al servidor
        const formData = new FormData();
        formData.append('obra_id', cuadroId);
        formData.append('comentario', comentario);

        fetch('php/procesar_comentario.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    const zonaComentarios = document.getElementById('zona-comentarios-' + cuadroId);
                    const btnDesplegar = zonaComentarios.querySelector('.btn-desplegar-comentarios');
                    const listaComentarios = document.getElementById('lista-comentarios-' + cuadroId);

                    // Añadir comentario visualmente con la etiqueta verde
                    const nuevoComentario = document.createElement('div');
                    nuevoComentario.classList.add('comentario-item');
                    nuevoComentario.innerHTML = `
                        <strong>${data.nombre_usuario}</strong>
                        <span style="color: #2ed573; font-size: 12px; margin-left: 5px; font-weight: bold;">(Tú) ✔</span>
                        <p style="margin: 5px 0 0 0;">${comentario}</p>
                    `;
                    listaComentarios.prepend(nuevoComentario);
                    input.value = '';

                    // Desbloquear si estaba bloqueado
                    if (btnDesplegar.classList.contains('bloqueado')) {
                        btnDesplegar.classList.remove('bloqueado');
                        btnDesplegar.innerHTML = `
                            <span>Comentarios de la comunidad</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M6 9l6 6 6-6"/></svg>
                        `;
                        listaComentarios.classList.add('abierto');
                    }

                    // ALERTA DE RECOMPENSA
                    if (data.recompensa_desbloqueada) {
                        alert("🎉 ¡Increíble! Has comentado en todas las obras y has desbloqueado un CUADRO SECRETO. Recargando la página...");
                        window.location.reload(); // Recarga para mostrar el nuevo cuadro de la base de datos
                    }
                } else {
                    alert("Error al enviar: " + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });
});