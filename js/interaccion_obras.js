document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // 0. RESTAURAR POSICIÓN O BAJAR A LA OBRA FINAL
    // =========================================================
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

    const bajarASecreta = sessionStorage.getItem('bajar_a_secreta') === 'true';
    const posicionGuardada = sessionStorage.getItem('posicion_scroll');

    if (bajarASecreta) {
        sessionStorage.removeItem('bajar_a_secreta');
        sessionStorage.removeItem('posicion_scroll');

        window.addEventListener('load', function () {
            setTimeout(() => {
                const destino = document.getElementById('mensaje-obra-final') || document.getElementById('obra-secreta');
                if (destino) destino.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 500);
        }, { once: true });

    } else if (posicionGuardada !== null) {
        sessionStorage.removeItem('posicion_scroll');

        window.addEventListener('load', function () {
            window.scrollTo({
                top: parseInt(posicionGuardada, 10),
                behavior: 'auto'
            });
        }, { once: true });
    }

    // =========================================================
    // 1. AUDIO DE LAS OBRAS
    // =========================================================
    const botonesAudio = document.querySelectorAll('.btn-play-pause');

    function pausarOtrosAudios(audioActual) {
        document.querySelectorAll('audio').forEach(audio => {
            if (audio !== audioActual && !audio.paused) {
                audio.pause();

                const botonCorrespondiente = document.querySelector(`.btn-play-pause[data-audio-id="${audio.id}"]`);

                if (botonCorrespondiente) {
                    botonCorrespondiente.textContent = audio.currentTime > 0
                        ? 'Iniciar audio'
                        : 'Escuchar la obra';
                }
            }
        });
    }

    botonesAudio.forEach(boton => {
        boton.addEventListener('click', function () {
            const audioId = this.getAttribute('data-audio-id');
            const audio = document.getElementById(audioId);

            if (!audio) return;

            if (audio.paused) {
                pausarOtrosAudios(audio);

                audio.play()
                    .then(() => {
                        this.textContent = 'Pausar audio';
                    })
                    .catch(error => {
                        console.error('No se ha podido reproducir el audio:', error);
                    });
            } else {
                audio.pause();
                this.textContent = 'Iniciar audio';
            }
        });
    });

    document.querySelectorAll('audio').forEach(audio => {
        audio.addEventListener('ended', function () {
            this.currentTime = 0;

            const boton = document.querySelector(`.btn-play-pause[data-audio-id="${this.id}"]`);

            if (boton) {
                boton.textContent = 'Escuchar la obra';
            }
        });
    });

    // =========================================================
    // 2. BOTÓN VOLVER A EMPEZAR
    // =========================================================
    const botonesReiniciar = document.querySelectorAll('.btn-reiniciar');

    botonesReiniciar.forEach(boton => {
        boton.addEventListener('click', function () {
            const audioId = this.getAttribute('data-audio-id');
            const audio = document.getElementById(audioId);

            if (!audio) return;

            pausarOtrosAudios(audio);
            audio.currentTime = 0;

            audio.play()
                .then(() => {
                    const botonAudio = document.querySelector(`.btn-play-pause[data-audio-id="${audioId}"]`);

                    if (botonAudio) {
                        botonAudio.textContent = 'Pausar audio';
                    }
                })
                .catch(error => {
                    console.error('No se ha podido reiniciar el audio:', error);
                });
        });
    });

    // =========================================================
    // 3. LEER / OCULTAR TEXTO DE LA OBRA
    // =========================================================
    const botonesTranscripcion = document.querySelectorAll('.btn-toggle-transcripcion');

    botonesTranscripcion.forEach(boton => {
        boton.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const transcripcion = document.getElementById(targetId);

            if (!transcripcion) return;

            const estaAbierta = transcripcion.classList.toggle('visible');

            this.textContent = estaAbierta ? 'Ocultar texto' : 'Leer la obra';
            this.setAttribute('aria-expanded', estaAbierta ? 'true' : 'false');
        });
    });

    // =========================================================
    // 4. DESPLEGAR COMENTARIOS
    // =========================================================
    const botonesComentarios = document.querySelectorAll('.btn-desplegar-comentarios');

    botonesComentarios.forEach(boton => {
        boton.addEventListener('click', function () {
            if (!this.classList.contains('bloqueado')) {
                const targetId = this.getAttribute('data-target');
                const listaComentarios = document.getElementById(targetId);

                if (listaComentarios) {
                    listaComentarios.classList.toggle('abierto');
                }
            }
        });
    });

    // =========================================================
    // 5. ENVIAR COMENTARIO REAL POR AJAX
    // =========================================================
    const formulariosComentario = document.querySelectorAll('.form-comentario');

    formulariosComentario.forEach(form => {
        form.addEventListener('submit', function (e) {
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
                        if (data.recompensa_desbloqueada && !document.getElementById('obra-secreta')) {
                            sessionStorage.setItem('bajar_a_secreta', 'true');
                            sessionStorage.removeItem('posicion_scroll');
                        } else {
                            sessionStorage.setItem('posicion_scroll', window.scrollY.toString());
                        }

                        window.location.reload();
                    } else {
                        alert('Error al enviar: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });

});