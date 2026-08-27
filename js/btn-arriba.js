const btnArriba = document.querySelector(".btn-volver-arriba");

if (btnArriba) {
    // Mostrar u ocultar el botón al hacer scroll
    window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
            btnArriba.classList.add("visible");
        } else {
            btnArriba.classList.remove("visible");
        }
    });

    // Comportamiento de subir de forma suave
    btnArriba.addEventListener("click", (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
}