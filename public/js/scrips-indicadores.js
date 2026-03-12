(function () {
    "use strict";
    //debemos crear la clase formEliminar dentro del form del boton borrar
    //recordar que cada registro a eliminar esta contenido en un form
    var forms = document.querySelectorAll(".formEliminar");
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                event.preventDefault();
                event.stopPropagation();
                Swal.fire({
                    title: "¿Confirma la eliminación del registro?",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#20c997",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Confirmar",
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                        Swal.fire(
                            "¡Eliminado!",
                            "El registro ha sido eliminado exitosamente.",
                            "success"
                        );
                    }
                });
            },
            false
        );
    });
})();
$(document).ready(function () {
    $("#tabla-indicadores").DataTable({
        pagingType: "simple_numbers",
        order: [],
        dom: "Bfrtip", // Define la disposición para que aparezcan los botones
        buttons: [
            {
                extend: "excelHtml5",
                text: "Exportar a Excel",
                className: "btn btn-success", // Clase de Bootstrap
            },
            {
                extend: "csvHtml5",
                text: "Exportar a CSV",
                className: "btn btn-primary", // Clase de Bootstrap
            },
            {
                extend: "pdfHtml5",
                text: "Exportar a PDF",
                className: "btn btn-danger", // Clase de Bootstrap
            },
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ entradas",
            info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            paginate: {
                previous: "Anterior",
                next: "Siguiente",
            },
        },
    });
});
$(document).ready(function () {
    $("#myTable").DataTable({
        pagingType: "simple_numbers",
        order: [],
        dom: "Bfrtip", // Define la disposición para que aparezcan los botones
        buttons: [
            {
                extend: "excelHtml5",
                text: "Exportar a Excel",
                className: "btn btn-success", // Clase de Bootstrap
            },
            {
                extend: "csvHtml5",
                text: "Exportar a CSV",
                className: "btn btn-primary", // Clase de Bootstrap
            },
            {
                extend: "pdfHtml5",
                text: "Exportar a PDF",
                className: "btn btn-danger", // Clase de Bootstrap
            },
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ entradas",
            info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            paginate: {
                previous: "Anterior",
                next: "Siguiente",
            },
        },
    });
});

const institucionSelect = document.getElementById("institucionSelect");
const contenedorTablaIndicadores = document.getElementById(
    "contenedor-tabla-indicadores"
);
const programa = document.getElementById("programa");

function selectIndicadores(url) {
    fetch(url)
        .then((response) => {
            if (response.ok) {
                return response.text();
            }
        })
        .then((data) => {
            // contenedorTablaIndicadores.innerHTML = data
            // Destruye DataTables si ya está inicializado
            if ($.fn.DataTable.isDataTable("#tabla-indicadores")) {
                $("#tabla-indicadores").DataTable().destroy();
            }

            // Reemplaza el contenido de la tabla
            contenedorTablaIndicadores.innerHTML = data;

            // Inicializa DataTables nuevamente
            $("#tabla-indicadores").DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    paginate: {
                        previous: "Anterior",
                        next: "Siguiente",
                    },
                },
                dom: "Bfrtip", // Define la posición de los botones (B = Buttons, f = search box, r = processing, t = table, i = info, p = pagination)
                buttons: [
                    {
                        extend: "copy", // Copiar al portapapeles
                        className: "btn btn-primary",
                        title: "indicadores_copia",
                    },
                    {
                        extend: "csv",
                        className: "btn btn-success",
                        title: "indicadores_csv",
                        filename: "indicadores_csv",
                    },
                    {
                        extend: "excel",
                        className: "btn btn-warning",
                        title: "indicadores_excel",
                        filename: "indicadores_excel",
                    },
                    {
                        extend: "pdf",
                        className: "btn btn-danger",
                        title: "indicadores_pdf",
                        filename: "indicadores_pdf",
                    },
                ],
            });
        })
        .catch((err) => {
            console.err(err);
        });
}

institucionSelect.addEventListener("change", (e) => {
    let idInstitucion = e.target.value;
    let url = `filtrar-indicadores/${idInstitucion}`;
    selectIndicadores(url);
});

programa.addEventListener("change", (e) => {
    let idInstitucion = institucionSelect.value;
    let programa = e.target.value;

    if (idInstitucion != "") {
        let url = `filtrar-indicadores/${idInstitucion}/${programa}`;
        selectIndicadores(url);
    } else {
        // Si no se selecciona una institución, muestra una alerta
        Swal.fire({
            icon: "warning",
            title: "¡Atención!",
            text: "Por favor, selecciona una institución primero.",
            confirmButtonText: "Entendido",
        }).then(() => {
            institucionSelect.focus(); // Enfoca el select de institución
        });
        // console.log("selecciona institución")
        // institucionSelect.focus()
    }
});
