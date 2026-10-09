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
    const siteHeader = document.querySelector(".site-header");
    const mainNav = document.getElementById("main-nav");
    const hamburgerMenu = document.getElementById("hamburger-menu");

    if (siteHeader && mainNav && "IntersectionObserver" in window) {
        const sentinel = document.createElement("div");
        sentinel.setAttribute("aria-hidden", "true");
        siteHeader.parentNode.insertBefore(sentinel, siteHeader);

        new IntersectionObserver(
            ([entry]) => mainNav.classList.toggle("is-sticky", !entry.isIntersecting),
            { threshold: 0, rootMargin: "-1px 0px 0px 0px" }
        ).observe(sentinel);
    }

    if (hamburgerMenu && mainNav) {
        const closeMobileMenu = () => {
            mainNav.classList.remove("active");
            hamburgerMenu.classList.remove("open");
            hamburgerMenu.setAttribute("aria-expanded", "false");
        };

        hamburgerMenu.addEventListener("click", () => {
            const isOpen = mainNav.classList.toggle("active");
            hamburgerMenu.classList.toggle("open", isOpen);
            hamburgerMenu.setAttribute("aria-expanded", String(isOpen));
        });

        document.querySelectorAll("#main-nav [data-menu-toggle]").forEach((toggle) => {
            toggle.addEventListener("click", (event) => {
                event.preventDefault();

                if (window.innerWidth <= 1199) {
                    const parent = toggle.parentElement;

                    Array.from(parent.parentElement.children).forEach((sibling) => {
                        if (sibling !== parent) {
                            sibling.classList.remove("active");
                        }
                    });

                    parent.classList.toggle("active");
                }
            });
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMobileMenu();
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 1199) {
                closeMobileMenu();
            }
        });
    }

    const scrollTopButton = document.querySelector(".scroll-top");

    if (scrollTopButton) {
        const progressCircle = scrollTopButton.querySelector(".scroll-top__value");
        const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
        const circumference = 2 * Math.PI * 18;

        if (progressCircle) {
            progressCircle.style.strokeDasharray = `${circumference} ${circumference}`;
        }

        const updateScrollTopButton = () => {
            const scrollTop = window.scrollY;
            const scrollableHeight = Math.max(
                document.documentElement.scrollHeight - window.innerHeight,
                0
            );
            const progress = scrollableHeight > 0
                ? Math.min(scrollTop / scrollableHeight, 1)
                : 0;

            scrollTopButton.classList.toggle("is-visible", scrollTop > 300);

            if (progressCircle) {
                progressCircle.style.strokeDashoffset = circumference * (1 - progress);
            }
        };

        window.addEventListener("scroll", updateScrollTopButton, { passive: true });
        scrollTopButton.addEventListener("click", (event) => {
            event.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: reducedMotion.matches ? "auto" : "smooth",
            });
        });
        updateScrollTopButton();
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
