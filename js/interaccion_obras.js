// 1. LÓGICA DE REPRODUCCIÓN DE AUDIO
const botonesAudio = document.querySelectorAll('.btn-play-pause');

botonesAudio.forEach(boton => {
    boton.addEventListener('click', function() {
        const audioId = this.getAttribute('data-audio-id');
        const audio = document.getElementById(audioId);

        const iconoPlay = this.querySelector('.icono-play');
        const iconoPause = this.querySelector('.icono-pause');

        if (audio.paused) {
            // Pausar todos los demás audios si hay uno sonando
            document.querySelectorAll('audio').forEach(a => {
                a.pause();
                // Restaurar iconos a Play
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

// 2. LÓGICA DE DESPLEGAR TRANSCRIPCIÓN
const botonesTranscripcion = document.querySelectorAll('.btn-toggle-transcripcion');

botonesTranscripcion.forEach(boton => {
    boton.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const cajaTexto = document.getElementById(targetId);

        cajaTexto.classList.toggle('visible');
        this.classList.toggle('abierto');
    });
});

// 3. LÓGICA DE DESBLOQUEO DE COMENTARIOS
const formulariosComentario = document.querySelectorAll('.form-comentario');

formulariosComentario.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Evitamos que la página recargue

        const input = this.querySelector('.input-comentario');
        if (input.value.trim() === '') return;

        const cuadroId = this.getAttribute('data-cuadro-id');
        const zonaComentarios = document.getElementById('zona-comentarios-' + cuadroId);
        const btnDesplegar = zonaComentarios.querySelector('.btn-desplegar-comentarios');
        const listaComentarios = document.getElementById('lista-comentarios-' + cuadroId);

        // 1. Añadimos el comentario nuevo a la lista visualmente
        const nuevoComentario = document.createElement('div');
        nuevoComentario.classList.add('comentario-item');
        nuevoComentario.innerHTML = `<strong>Tú:</strong> ${input.value}`;
        listaComentarios.prepend(nuevoComentario); // Lo pone el primero

        // 2. Limpiamos el input
        input.value = '';

        // 3. DESBLOQUEAMOS LA ZONA
        if (btnDesplegar.classList.contains('bloqueado')) {
            btnDesplegar.classList.remove('bloqueado');

            // Cambiar el icono del candado por una flecha hacia abajo
            btnDesplegar.innerHTML = `
                    <span>Comentarios de la comunidad</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M6 9l6 6 6-6"/></svg>
                `;

            // Añadimos la función para poder abrir/cerrar los comentarios ahora que está desbloqueado
            btnDesplegar.addEventListener('click', function() {
                listaComentarios.classList.toggle('abierto');
            });

            // Lo abrimos automáticamente para que vea su comentario
            listaComentarios.classList.add('abierto');
        }
    });
});