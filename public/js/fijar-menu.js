document.addEventListener('DOMContentLoaded', function() {
    posicionarMenu();

    window.addEventListener('scroll', posicionarMenu);

    function posicionarMenu() {
        var header = document.querySelector('.index-header');
        var menu = document.querySelector('.menu');

        if (!header || !menu) {
            console.error('No se encontró el header o el menú en el DOM.');
            return;
        }

        var alturaDelHeader = header.offsetHeight;

        if (window.scrollY >= alturaDelHeader) {
            menu.classList.add('fixed');
        } else {
            menu.classList.remove('fixed');
        }
    }
});
