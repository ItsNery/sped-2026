// Se unifican los scripts de la vista de administrador
document.addEventListener("DOMContentLoaded", function () {
    // IDs de los contenedores
    const containerIds = [
        "institucionesSinIndicadores",
        "indicadoresRecientes",
    ];
    let scrollIntervals = {}; // Almacena los intervalos de cada contenedor

    // Función para iniciar el scroll automático
    function startScroll(container) {
        if (!scrollIntervals[container.id]) {
            scrollIntervals[container.id] = setInterval(
                () => autoScroll(container),
                50
            ); // Ajusta la velocidad del scroll
        }
    }

    // Función para detener el scroll automático
    function stopScroll(container) {
        if (scrollIntervals[container.id]) {
            clearInterval(scrollIntervals[container.id]);
            scrollIntervals[container.id] = null;
        }
    }

    // Función para realizar el scroll automático
    function autoScroll(container) {
        if (container.scrollHeight > container.clientHeight) {
            container.scrollBy(0, 1);
            if (
                container.scrollTop + container.clientHeight >=
                container.scrollHeight
            ) {
                container.scrollTo(0, 0); // Reinicia el scroll
            }
        }
    }

    // Inicializar el comportamiento para cada contenedor
    containerIds.forEach((id) => {
        const container = document.getElementById(id);
        if (container) {
            // Iniciar el scroll automático al cargar la página
            startScroll(container);

            // Pausar el scroll cuando el mouse entra en el contenedor
            container.addEventListener("mouseenter", () =>
                stopScroll(container)
            );

            // Reanudar el scroll cuando el mouse sale del contenedor
            container.addEventListener("mouseleave", () =>
                startScroll(container)
            );
        }
    });
});
// Buscador en indicadores
document.addEventListener("DOMContentLoaded", function () {
    // Selecciona el input de búsqueda
    const searchInput = document.querySelector(".search-input");

    // Comprueba si se encontró el elemento
    if (searchInput) {
        // Escucha el evento 'keyup' en el input
        searchInput.addEventListener("keyup", function () {
            // Obtén el texto de búsqueda en minúsculas
            const searchText = this.value.toLowerCase();

            // Obtén la clase objetivo desde el atributo 'data-target'
            const targetClass = this.getAttribute("data-target");

            // Selecciona todos los elementos con la clase objetivo y la clase 'list-group-item'
            const items = document.querySelectorAll(
                `.${targetClass} .list-group-item`
            );

            // Recorre cada elemento
            items.forEach(function (item) {
                // Obtén el texto del elemento en minúsculas
                const text = item.textContent.toLowerCase();

                // Muestra u oculta el elemento según si coincide con el texto de búsqueda
                item.style.display = text.includes(searchText) ? "" : "none";
            });
        });
    } 
});

