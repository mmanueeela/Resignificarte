// Seleccionamos el input del buscador y todas las tarjetas de experiencia
const buscadorInput = document.querySelector(".buscador input");
const experiencias = document.querySelectorAll(".experiencia-1");

if (buscadorInput) {
    // Escuchamos cada vez que el usuario escribe una letra
    buscadorInput.addEventListener("input", function(e) {
        // Convertimos lo que escribe a minúsculas para que no importe si usa mayúsculas
        const filtro = e.target.value.toLowerCase().trim();

        experiencias.forEach(experiencia => {
            // Sacamos el nombre del artista directamente del atributo 'alt' de su foto
            const imagen = experiencia.querySelector(".contenedor-imagen-mas-texto img");
            const nombreArtista = imagen ? imagen.alt.toLowerCase() : "";

            // Si el nombre del artista incluye lo que hemos escrito...
            if (nombreArtista.includes(filtro)) {
                experiencia.style.display = "flex"; // Lo mostramos
            } else {
                experiencia.style.display = "none"; // Lo ocultamos
            }
        });
    });
}