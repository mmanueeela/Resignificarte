document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // 0. COMPROBAR SI VENIMOS DE DESBLOQUEAR LA OBRA FINAL
    // =========================================================
    if (sessionStorage.getItem('bajar_a_secreta') === 'true') {
        sessionStorage.removeItem('bajar_a_secreta');

        setTimeout(() => {
            const destino = document.getElementById('mensaje-obra-final') || document.getElementById('obra-secreta');

            if (destino) {
                destino.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 500);
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
                        const zonaComentarios = document.getElementById('zona-comentarios-' + cuadroId);
                        const btnDesplegar = zonaComentarios.querySelector('.btn-desplegar-comentarios');
                        const listaComentarios = document.getElementById('lista-comentarios-' + cuadroId);

                        const nuevoComentario = document.createElement('div');
                        nuevoComentario.classList.add('comentario-item');

                        const nombreUsuario = document.createElement('strong');
                        nombreUsuario.textContent = data.nombre_usuario;

                        const etiquetaTu = document.createElement('span');
                        etiquetaTu.textContent = '(Tú)';
                        etiquetaTu.style.color = '#2ed573';
                        etiquetaTu.style.fontSize = '12px';
                        etiquetaTu.style.marginLeft = '5px';
                        etiquetaTu.style.fontWeight = 'bold';

                        const textoComentario = document.createElement('p');
                        textoComentario.textContent = comentario;
                        textoComentario.style.margin = '5px 0 0 0';

                        nuevoComentario.appendChild(nombreUsuario);
                        nuevoComentario.appendChild(etiquetaTu);
                        nuevoComentario.appendChild(textoComentario);
                        listaComentarios.prepend(nuevoComentario);

                        input.value = '';

                        const badge = document.getElementById('badge-comentado-' + cuadroId);

                        if (badge) {
                            badge.classList.add('visible');
                        }

                        if (btnDesplegar.classList.contains('bloqueado')) {
                            btnDesplegar.classList.remove('bloqueado');

                            btnDesplegar.innerHTML = `
                                <span>Comentarios de la comunidad</span>
                                <svg class="flecha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"></path>
                                </svg>
                            `;

                            listaComentarios.classList.add('abierto');
                        }

                        if (data.recompensa_desbloqueada && !document.getElementById('obra-secreta')) {
                            sessionStorage.setItem('bajar_a_secreta', 'true');
                            window.location.reload();
                        }
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