const btnVolver = document.querySelector('.btn-volver-arriba');

window.addEventListener('scroll', () => {
    const alturaAparecer = 300;
    const alturaDesaparecer = 200;

    if (window.scrollY >= alturaAparecer) {
        btnVolver.classList.add('visible');
    }

    if (window.scrollY < alturaDesaparecer) {
        btnVolver.classList.remove('visible');
    }
});

const btnMenu = document.getElementById('btn-menu');
const menuMobile = document.getElementById('menu-mobile');

btnMenu.addEventListener('click', () => {
    btnMenu.classList.toggle('activo');
    menuMobile.classList.toggle('abierto');
});