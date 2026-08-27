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

// 3. Cierra el menú desplegable si el usuario vuelve atrás con la flecha del navegador
window.addEventListener('pageshow', function(event) {
    if (dropdown && dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
    }
});

// 4. Cierra el menú en el momento en que haces clic en "Ver perfil" o "Cerrar sesión"
const enlacesDropdown = document.querySelectorAll('.dropdown-item');
enlacesDropdown.forEach(enlace => {
    enlace.addEventListener('click', () => {
        if (dropdown) {
            dropdown.classList.remove('show');
        }
    });
});