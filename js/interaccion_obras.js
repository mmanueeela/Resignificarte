// =========================================================
// 0. COMPROBAR SI VENIMOS DE DESBLOQUEAR LA RECOMPENSA
// =========================================================
if (sessionStorage.getItem('bajar_a_secreta') === 'true') {
    sessionStorage.removeItem('bajar_a_secreta');
    setTimeout(() => {
        const cuadroSecreto = document.getElementById('obra-secreta');
        if (cuadroSecreto) {
            cuadroSecreto.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 300);
}

// =========================================================
// 1. REPRODUCCIÓN AUDIO, REINICIO Y TRANSCRIPCIÓN
// =========================================================
const botonesAudio = document.querySelectorAll('.btn-play-pause');
botonesAudio.forEach(boton => {
    boton.addEventListener('click', function() {
        const audioId = this.getAttribute('data-audio-id');
        const audio = document.getElementById(audioId);
        const iconoPlay = this.querySelector('.icono-play');
        const iconoPause = this.querySelector('.icono-pause');

        if (audio.paused) {
            // Pausar todos los demás audios
            document.querySelectorAll('audio').forEach(a => {
                a.pause();
                const container = document.querySelector(`.btn-play-pause[data-audio-id="${a.id}"]`);
                if(container) {
                    container.querySelector('.icono-play').style.display = 'block';
                    container.querySelector('.icono-pause').style.display = 'none';
                }
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

// ARREGLO: Cuando un audio termina, devolver el icono a 'Play'
document.querySelectorAll('audio').forEach(audio => {
    audio.addEventListener('ended', function() {
        const boton = document.querySelector(`.btn-play-pause[data-audio-id="${this.id}"]`);
        if (boton) {
            boton.querySelector('.icono-play').style.display = 'block';
            boton.querySelector('.icono-pause').style.display = 'none';
        }
    });
});

// NUEVO: Botón de reiniciar
const botonesReiniciar = document.querySelectorAll('.btn-reiniciar');
botonesReiniciar.forEach(boton => {
    boton.addEventListener('click', function() {
        const audioId = this.getAttribute('data-audio-id');
        const audio = document.getElementById(audioId);

        // Devuelve el audio al segundo 0
        audio.currentTime = 0;
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

// =========================================================
// 2. DESPLEGAR COMENTARIOS
// =========================================================
const botonesComentarios = document.querySelectorAll('.btn-desplegar-comentarios');
botonesComentarios.forEach(boton => {
    boton.addEventListener('click', function() {
        if (!this.classList.contains('bloqueado')) {
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.toggle('abierto');
        }
    });
});

// =========================================================
// 3. ENVIAR COMENTARIO REAL POR AJAX
// =========================================================
const formulariosComentario = document.querySelectorAll('.form-comentario');
formulariosComentario.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const input = this.querySelector('.input-comentario');
        const comentario = input.value.trim();
        if (comentario === '') return;

        const cuadroId = this.getAttribute('data-cuadro-id');

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

                    // Añadir comentario visualmente
                    const nuevoComentario = document.createElement('div');
                    nuevoComentario.classList.add('comentario-item');
                    nuevoComentario.innerHTML = `
                        <strong>${data.nombre_usuario}</strong>
                        <span style="color: #2ed573; font-size: 12px; margin-left: 5px; font-weight: bold;">(Tú) ✔</span>
                        <p style="margin: 5px 0 0 0;">${comentario}</p>
                    `;
                    listaComentarios.prepend(nuevoComentario);
                    input.value = '';
                    const badge = document.getElementById('badge-comentado-' + cuadroId);
                    if (badge) {
                        badge.classList.add('visible');
                    }

                    // Desbloquear zona si estaba bloqueada
                    if (btnDesplegar.classList.contains('bloqueado')) {
                        btnDesplegar.classList.remove('bloqueado');
                        btnDesplegar.innerHTML = `
                            <span>Comentarios de la comunidad</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M6 9l6 6 6-6"/></svg>
                        `;
                        listaComentarios.classList.add('abierto');
                    }

                    // ALERTA DE RECOMPENSA
                    if (data.recompensa_desbloqueada && !document.getElementById('obra-secreta')) {
                        const popup = document.getElementById('popup-recompensa');
                        if (popup) {
                            popup.classList.add('activo');

                            document.getElementById('btn-cerrar-popup-recompensa').addEventListener('click', function() {
                                sessionStorage.setItem('bajar_a_secreta', 'true');
                                window.location.reload();
                            });
                        } else {
                            alert("🎉 ¡Increíble! Has comentado en todas las obras y has desbloqueado un CUADRO SECRETO.");
                            sessionStorage.setItem('bajar_a_secreta', 'true');
                            window.location.reload();
                        }
                    }
                } else {
                    alert("Error al enviar: " + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });
});