document.addEventListener("DOMContentLoaded", function () {
    const button = document.getElementById("floatingUserButton");
    const dropdown = document.getElementById("floatingUserDropdown");

    if (!button || !dropdown) return;

    button.addEventListener("click", function (event) {
        event.stopPropagation();

        const isOpen = dropdown.classList.toggle("show");

        button.setAttribute("aria-expanded", isOpen);
        dropdown.setAttribute("aria-hidden", !isOpen);
    });

    document.addEventListener("click", function (event) {
        if (!event.target.closest(".floating-user-menu")) {
            dropdown.classList.remove("show");
            button.setAttribute("aria-expanded", "false");
            dropdown.setAttribute("aria-hidden", "true");
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            dropdown.classList.remove("show");
            button.setAttribute("aria-expanded", "false");
            dropdown.setAttribute("aria-hidden", "true");
            button.focus();
        }
    });
});
window.addEventListener("load", function () {
    let modal = document.getElementById("customSearchModal");

    let lastFocusedElement = null;
    let searchTimeout = null;
    let searchRequest = null;
    const searchInput = document.getElementById("indicatorSearchInput");
    const searchStatus = document.getElementById("indicatorSearchStatus");
    const searchResults = document.getElementById("indicatorSearchResults");

    function renderIndicatorResults(items) {
        searchResults.innerHTML = "";

        if (!items.length) {
            searchStatus.textContent =
                "No encontramos indicadores con ese criterio.";
            return;
        }

        searchStatus.textContent =
            items.length === 10
                ? "Mostrando los primeros 10 resultados."
                : items.length +
                  (items.length === 1
                      ? " indicador encontrado."
                      : " indicadores encontrados.");

        items.forEach(function (item) {
            const link = document.createElement("a");
            link.className = "indicator-search-result";
            link.href = item.url;

            const name = document.createElement("strong");
            name.textContent = item.nombre;
            link.appendChild(name);

            const context = document.createElement("span");
            context.textContent = [item.contexto, item.institucion]
                .filter(Boolean)
                .join(" · ");
            link.appendChild(context);

            searchResults.appendChild(link);
        });
    }

    function searchIndicators(value) {
        const query = value.trim();
        searchResults.innerHTML = "";

        if (query.length < 2) {
            searchStatus.textContent =
                "Escribe al menos dos caracteres para buscar.";
            return;
        }

        if (searchRequest) searchRequest.abort();
        searchStatus.textContent = "Buscando indicadores...";
        searchRequest = new AbortController();

        fetch(
            window.AppRoutes.buscarIndicadores +
                "?q=" +
                encodeURIComponent(query),
            {
                headers: {
                    Accept: "application/json",
                },
                signal: searchRequest.signal,
            },
        )
            .then(function (response) {
                if (!response.ok) throw new Error("Search request failed");
                return response.json();
            })
            .then(function (payload) {
                renderIndicatorResults(payload.data || []);
            })
            .catch(function (error) {
                if (error.name !== "AbortError")
                    searchStatus.textContent =
                        "No fue posible realizar la búsqueda.";
            });
    }

    window.openSearchModal = function (event) {
        event?.preventDefault();
        lastFocusedElement = document.activeElement;
        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
        searchInput.value = "";
        searchResults.innerHTML = "";
        searchStatus.textContent =
            "Escribe al menos dos caracteres para buscar.";
        searchInput.focus();
    };

    window.closeSearchModal = function () {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
        if (searchRequest) searchRequest.abort();
        lastFocusedElement?.focus();
    };

    searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            searchIndicators(searchInput.value);
        }, 250);
    });

    document
        .getElementById("indicatorSearchForm")
        .addEventListener("submit", function (event) {
            event.preventDefault();
            clearTimeout(searchTimeout);
            searchIndicators(searchInput.value);
        });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && modal.classList.contains("show")) {
            closeSearchModal();
        }
    });

    window.onclick = function (event) {
        if (event.target === modal) {
            closeSearchModal();
        }
    };
});
