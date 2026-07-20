const enlaces = document.querySelectorAll('header a');

enlaces.forEach(enlace => {
    enlace.addEventListener('click', function(e) {
        // Obtenemos la URL a la que apunta el enlace
        const destino = this.getAttribute('href');

        // Si el enlace está vacío o es solo "#", dejamos que haga su comportamiento normal para no dar error.
        if (!destino || destino === '#') return;

        // Bloqueamos el cambio de página inmediato
        e.preventDefault();

        // Esperamos 100ms (0.1s) y ejecutamos la redirección
        setTimeout(() => {
            window.location.href = destino;
        }, 100);
    });
});