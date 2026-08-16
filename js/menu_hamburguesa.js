const btnMenu = document.getElementById('btn-menu');
const menuMobile = document.getElementById('menu-mobile');

btnMenu.addEventListener('click', () => {
    btnMenu.classList.toggle('activo');
    menuMobile.classList.toggle('abierto');
});