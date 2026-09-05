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
    // 5. CONFIRMAR Y ENVIAR COMENTARIO
    // =========================================================
    const formulariosComentario = document.querySelectorAll('.form-comentario');
    const modalComentario = document.getElementById('modal-confirmar-comentario');
    const textoConfirmacion = document.getElementById('texto-comentario-confirmacion');
    const btnCancelarComentario = document.getElementById('btn-cancelar-comentario');
    const btnConfirmarComentario = document.getElementById('btn-confirmar-comentario');
    const errorModalComentario = document.getElementById('error-modal-comentario');
    const bloqueSorteoAntonio = document.getElementById('bloque-sorteo-antonio');
    const inputImagenSorteo = document.getElementById('imagen-sorteo-antonio');
    const nombreImagenSorteo = document.getElementById('nombre-imagen-sorteo');

    let formularioPendiente = null;
    let comentarioPendiente = '';

    function abrirModalComentario(form, comentario) {
        formularioPendiente = form;
        comentarioPendiente = comentario;

        const tieneSorteo = form.dataset.sorteo === '1';

        textoConfirmacion.textContent = comentario;
        errorModalComentario.textContent = '';
        errorModalComentario.classList.remove('visible');

        bloqueSorteoAntonio.hidden = !tieneSorteo;
        inputImagenSorteo.value = '';
        nombreImagenSorteo.textContent = 'Ninguna imagen seleccionada';

        modalComentario.classList.add('activo');
        modalComentario.setAttribute('aria-hidden', 'false');
        btnConfirmarComentario.focus();
    }

    function cerrarModalComentario() {
        modalComentario.classList.remove('activo');
        modalComentario.setAttribute('aria-hidden', 'true');
        btnConfirmarComentario.disabled = false;

        errorModalComentario.textContent = '';
        errorModalComentario.classList.remove('visible');

        bloqueSorteoAntonio.hidden = true;
        inputImagenSorteo.value = '';
        nombreImagenSorteo.textContent = 'Ninguna imagen seleccionada';

        formularioPendiente = null;
        comentarioPendiente = '';
    }

    formulariosComentario.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = this.querySelector('.input-comentario');
            const comentario = input.value.trim();
            if (comentario === '') return;
            abrirModalComentario(this, comentario);
        });
    });

    btnCancelarComentario.addEventListener('click', cerrarModalComentario);

    modalComentario.addEventListener('click', function(e) {
        if (e.target === modalComentario) cerrarModalComentario();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalComentario.classList.contains('activo')) cerrarModalComentario();
    });

    inputImagenSorteo.addEventListener('change', function() {
        errorModalComentario.textContent = '';
        errorModalComentario.classList.remove('visible');

        if (this.files.length === 0) {
            nombreImagenSorteo.textContent = 'Ninguna imagen seleccionada';
            return;
        }

        const archivo = this.files[0];
        const maximo = 5 * 1024 * 1024;
        const tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

        if (!tiposPermitidos.includes(archivo.type)) {
            this.value = '';
            nombreImagenSorteo.textContent = 'Ninguna imagen seleccionada';
            errorModalComentario.textContent = 'Solo se permiten imágenes JPG, PNG o WEBP.';
            errorModalComentario.classList.add('visible');
            return;
        }

        if (archivo.size > maximo) {
            this.value = '';
            nombreImagenSorteo.textContent = 'Ninguna imagen seleccionada';
            errorModalComentario.textContent = 'La imagen no puede superar los 5 MB.';
            errorModalComentario.classList.add('visible');
            return;
        }

        nombreImagenSorteo.textContent = archivo.name;
    });

    btnConfirmarComentario.addEventListener('click', function() {
        if (!formularioPendiente || comentarioPendiente === '') return;

        const cuadroId = formularioPendiente.getAttribute('data-cuadro-id');
        const tieneSorteo = formularioPendiente.dataset.sorteo === '1';
        const formData = new FormData();

        formData.append('obra_id', cuadroId);
        formData.append('comentario', comentarioPendiente);

        if (tieneSorteo && inputImagenSorteo.files.length > 0) {
            formData.append('imagen_sorteo', inputImagenSorteo.files[0]);
        }

        btnConfirmarComentario.disabled = true;

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
                    btnConfirmarComentario.disabled = false;
                    errorModalComentario.textContent = 'Error al enviar: ' + data.error;
                    errorModalComentario.classList.add('visible');
                }
            })
            .catch(error => {
                btnConfirmarComentario.disabled = false;
                errorModalComentario.textContent = 'Ha ocurrido un error al enviar el comentario. Inténtalo de nuevo.';
                errorModalComentario.classList.add('visible');
                console.error('Error:', error);
            });
    });

});