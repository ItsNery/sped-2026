// Esta función parece obsoleta o para una estructura de menú diferente,
// ya que la lógica principal del menú móvil está más abajo y usa IDs específicos.
// Si no la estás usando, considera eliminarla para evitar confusiones.
/*
function toggleMenu() {
    const menu = document.querySelector(".nav.menu");
    menu.classList.toggle("active");
}
*/

// La siguiente sección para 'subdropbtns' ya estaba comentada por ti.
// La mantenemos así, ya que es para una estructura de submenús diferente
// a la que usa Bootstrap con 'data-bs-toggle="dropdown"'.
// document.addEventListener("DOMContentLoaded", () => {
//     const subdropbtns = document.querySelectorAll(".submenu > .subdropbtn");
//     subdropbtns.forEach((btn) => {
//         btn.addEventListener("click", (e) => {
//             if (window.innerWidth <= 768) {
//                 e.stopPropagation();
//                 const allSubContents = document.querySelectorAll(".submenu-content");
//                 allSubContents.forEach((sub) => {
//                     if (sub !== btn.nextElementSibling) {
//                         sub.style.display = "none";
//                     }
//                 });
//                 const subContent = btn.nextElementSibling;
//                 if (subContent.style.display === "block") {
//                     subContent.style.display = "none";
//                 } else {
//                     subContent.style.display = "block";
//                 }
//             }
//         });
//     });
//     document.addEventListener("click", () => {
//         if (window.innerWidth <= 768) {
//             const allSubContents = document.querySelectorAll(".submenu-content");
//             allSubContents.forEach((sub) => {
//                 sub.style.display = "none";
//             });
//         }
//     });
// });

document.addEventListener("DOMContentLoaded", function () {
    // --- Lógica para el Header Fijo (Sticky) ---
    const navbar = document.getElementById("navbar");
    // Si ".encabezado-movil" es la barra específica dentro de #navbar que quieres que reaccione,
    // o si es #navbar el que recibe las clases de sticky, ajusta según sea necesario.
    // Aquí asumimos que las clases de sticky se aplican a #navbar.

    if (navbar) { // Solo ejecutar si el navbar existe
        window.addEventListener(
            "scroll",
            () => {
                const currentScroll =
                    window.pageYOffset || document.documentElement.scrollTop;

                if (currentScroll > 50) { // Umbral para activar el sticky
                    navbar.classList.add("fixed-top");
                    // Aquí puedes usar tus clases personalizadas como "navbar-scrolled-custom"
                    // o las clases de Bootstrap directamente si prefieres.
                    navbar.classList.add("navbar-scrolled-custom");
                    // Ejemplo con clases de Bootstrap:
                } else {
                    navbar.classList.remove("fixed-top");
                    navbar.classList.remove("navbar-scrolled-custom");
                    navbar.classList.remove("bg-light");
                    navbar.classList.remove("shadow-sm");
                }
            },
            false
        );
    }

    // --- Lógica para el Menú Móvil (Toggle) ---
    const menuButton = document.getElementById("menuButton");
    const menuOverlay = document.getElementById("menuOverlay");
    const mobileMenu = document.getElementById("mobileMenu");
    const closeMenuButton = document.getElementById("closeMenuButton"); // Botón de cierre dentro del menú

    function openMobileMenu() {
        if (mobileMenu) mobileMenu.classList.add("active");
        if (menuOverlay) menuOverlay.classList.add("active");
        if (menuButton) menuButton.setAttribute("aria-expanded", "true");
        // Opcional: Mover el foco para accesibilidad
        // if (closeMenuButton) closeMenuButton.focus();
    }

    function closeMobileMenu() {
        if (mobileMenu) mobileMenu.classList.remove("active");
        if (menuOverlay) menuOverlay.classList.remove("active");
        if (menuButton) menuButton.setAttribute("aria-expanded", "false");
        // Opcional: Devolver el foco
        // if (menuButton) menuButton.focus();
    }

    if (menuButton && mobileMenu && menuOverlay && closeMenuButton) {
        menuButton.addEventListener("click", function () {
            const isMenuActive = mobileMenu.classList.contains("active");
            if (isMenuActive) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        menuOverlay.addEventListener("click", closeMobileMenu);
        closeMenuButton.addEventListener("click", closeMobileMenu);

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                mobileMenu.classList.contains("active")
            ) {
                closeMobileMenu();
            }
        });
    } else {
        console.warn(
            "Advertencia: No se encontraron uno o más elementos del menú móvil principal (botón, overlay, contenedor o botón de cierre). La funcionalidad del menú móvil podría estar afectada."
        );
    }

    // // --- NUEVA LÓGICA: Mostrar menú automáticamente en la primera visita a la homepage ---
    // const isHomepage = window.location.pathname === '/';
    // const menuAutoOpenedKey = 'menuHasBeenAutoOpened_v1'; // Puedes cambiar '_v1' si alguna vez quieres resetear esto para todos los usuarios

    // if (isHomepage && !localStorage.getItem(menuAutoOpenedKey)) {
    //     // Asegurarnos de que los elementos del menú existen antes de intentar abrirlos
    //     // (ya deberían estar definidos por el código anterior del menú móvil)
    //     if (mobileMenu && menuOverlay && menuButton) {
    //         // console.log("Abriendo menú automáticamente por primera vez en la homepage."); // Para depuración
    //         openMobileMenu(); // Llama a la función que ya tienes para abrir el menú
    //         localStorage.setItem(menuAutoOpenedKey, 'true'); // Marca que el menú ya se abrió automáticamente
    //     }
    // }
    // --- FIN DE LA NUEVA LÓGICA ---

    // --- Lógica para Dropdowns dentro del menú móvil ---
    // Si estás usando Bootstrap 5 y sus atributos data-bs-toggle="dropdown",
    // Bootstrap se encargará de la lógica de los dropdowns internos.
    // No necesitas JavaScript adicional aquí para esa funcionalidad básica de Bootstrap,
    // siempre y cuando bootstrap.bundle.js esté cargado y funcionando.

}); // Fin del DOMContentLoaded

window.addEventListener("load", function () {
    const modal = document.getElementById("customSearchModal"); // Asegúrate que el ID sea correcto

    // Solo definir funciones y listeners si el modal existe
    if (modal) {
        window.openSearchModal = function () {
            modal.classList.add("show");
            // Si tu CSS para .show no usa display: block, podrías añadirlo aquí:
            // modal.style.display = "block";
        };

        window.closeSearchModal = function () {
            modal.classList.remove("show");
            // modal.style.display = "none";
        };

        // Cerrar el modal si se hace clic fuera de su contenido directo (en el overlay del modal)
        // Esto asume que 'modal' es el elemento que se oscurece o el contenedor más externo del modal.
        // Si tu modal tiene una estructura interna (ej. .modal-content), ajusta el target.
        window.addEventListener("click", function (event) {
            if (event.target === modal) { // Si el clic fue directamente en el overlay del modal
                closeSearchModal();
            }
        });

        // Ejemplo para un botón de cierre explícito dentro del modal:
        // const closeModalButtonInside = modal.querySelector('.btn-close-modal-search'); // Cambia '.btn-close-modal-search' por tu selector real
        // if (closeModalButtonInside) {
        //     closeModalButtonInside.addEventListener('click', closeSearchModal);
        // }

    } else {
        // console.warn("Advertencia: Modal de búsqueda 'customSearchModal' no encontrado.");
    }
}); // Fin del window.addEventListener("load", ...)
