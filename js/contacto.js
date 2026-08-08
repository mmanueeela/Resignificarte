// Texto de enviado
const formulario = document.getElementById("form-contacto");

if(formulario) {
    formulario.addEventListener("submit", () => {
        const boton = formulario.querySelector("button");
        boton.innerHTML = "ENVIANDO...";
        boton.disabled = true;
    });
}

// Mensaje en pantalla
const params = new URLSearchParams(window.location.search);
const mensaje = document.getElementById("mensaje-contacto");

if (mensaje) {
    if (params.has("exito")) {
        mensaje.innerHTML = "Mensaje enviado correctamente.";
        mensaje.classList.add("exito");
    } else if (params.has("error")) {
        mensaje.innerHTML = "Ha ocurrido un error enviando el mensaje.";
        mensaje.classList.add("error");
    }
}