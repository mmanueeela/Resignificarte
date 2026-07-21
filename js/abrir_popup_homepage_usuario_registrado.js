const btnUsuario = document.getElementById('btn-usuario');
const dropdown = document.getElementById('dropdown-usuario');

if (btnUsuario && dropdown) {
    // 1. Mostrar/Ocultar al hacer clic en el nombre
    btnUsuario.addEventListener('click', function(evento) {
        evento.stopPropagation(); // Evita que el clic se propague al resto de la página
        dropdown.classList.toggle('show');
    });

    // 2. Ocultar el menú si hacemos clic en cualquier otro lado de la pantalla
    document.addEventListener('click', function(evento) {
        // Si el menú está abierto y el clic no ha sido dentro del menú...
        if (dropdown.classList.contains('show') && !dropdown.contains(evento.target)) {
            dropdown.classList.remove('show');
        }
    });
}