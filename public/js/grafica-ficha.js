   document.addEventListener("DOMContentLoaded", function() {

        //======================================================================

        // Función de Formateo Numérico para JavaScript

        //======================================================================

        function formatNumberForApex(value, decimalPlaces = 2) {

            if (value === null || value === undefined) {

                return "N/D"; // O una cadena vacía '' si prefieres no mostrar nada

            }

            const num = parseFloat(value);

            if (isNaN(num)) {

                // Si no es un número, devuelve el valor original si es un string no vacío, sino "N/D"

                return typeof value === 'string' && value.trim() !== '' ? value : "N/D";

            }

            // Usar 'en-US' para forzar coma como separador de miles y punto como decimal (ej: 1,234.56)

            try {

                return num.toLocaleString('en-US', {

                    minimumFractionDigits: decimalPlaces,

                    maximumFractionDigits: decimalPlaces

                });

            } catch (e) {

                // Fallback manual si toLocaleString no estuviera disponible o fallara (muy raro)

                const parts = num.toFixed(decimalPlaces).split('.');

                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                return parts.join('.');

            }

        }





        const nombreIndicadorJS = @json($nombreIndicadorParaGrafica_js);

        const unidadMedidaJS = @json($unidadMedidaParaGrafica_js);

        const colorIndicadorJS = @json($colorIndicadorParaGrafica_js);

        const anioLBJS = @json($anioLB_js); // Año de la Línea Base como string o null

        const nombreSerieLineaBaseJS = @json($nombreSerieLineaBase_php);



        const datosPrincipalesJS = @json($datosParaGraficaPrincipal_php);

        const categoriasEjeXJS = @json($categoriasEjeX_php);

        const datosMetaJS = @json($datosMetaPunto_php);

        const datosLineaBaseJS = @json($datosLineaBasePunto_php);



        var options = {

            // --- SERIES DE DATOS ---

            series: [{

                    // Serie 1: Línea Base (se mostrará como un punto debido a los datos)

                    name: nombreSerieLineaBaseJS,

                    data: datosLineaBaseJS,

                    type: 'line', // Tipo línea, pero con datos null excepto en un punto

                    stroke: {

                        width: 2,

                        dashArray: 5, // Línea punteada o discontinua para diferenciarla

                        curve: 'straight'

                    },

                    markers: {

                        size: 6, // Tamaño del marcador en el punto de la línea base

                        strokeWidth: 1,

                        strokeColors: ['#000000'], // Borde negro para el marcador

                        fillColors: ['#00E396'], // Relleno verde

                        hover: {

                            size: 8

                        }

                    },

                    tooltip: { // Tooltip específico si el general no es suficiente o para anularlo

                        y: {

                            formatter: function(val) {

                                return val !== null ? formatNumberForApex(val) : undefined;

                            },

                            title: {

                                formatter: function(seriesName) {

                                    return seriesName + ':';

                                }

                            }

                        }

                    }

                },

                {

                    // Serie 2: Datos Históricos (Línea principal)

                    name: unidadMedidaJS,

                    data: datosPrincipalesJS,

                    type: 'line'

                },

                {

                    // Serie 3: Meta 2030 (se mostrará como un punto)

                    name: 'Meta 2030',

                    data: datosMetaJS,

                    type: 'line', // Tipo línea, pero con datos null excepto en un punto

                    dataLabels: {

                        enabled: true,

                        formatter: function(val, opts) {

                            // Formatear el valor que se muestra en la etiqueta

                            return val !== null ? formatNumberForApex(val) :

                                ''; // No mostrar "N/D", solo el valor o nada

                        },

                        offsetY: -15, // Ajustar la posición vertical de la etiqueta

                        style: {

                            fontSize: '11px', // Tamaño de fuente

                            colors: ["#333"] // Color del texto de la etiqueta

                        },

                        background: { // Fondo para las etiquetas para mejor legibilidad

                            enabled: true,

                            foreColor: '#333', // Color del texto sobre el fondo

                            padding: 3,

                            borderRadius: 2,

                            borderWidth: 1,

                            borderColor: 'rgba(180,180,180,0.6)',

                            opacity: 0.8,

                        }

                    },

                    stroke: {

                        width: 2,

                        dashArray: 5, // Línea punteada o discontinua

                        curve: 'straight'

                    },

                    markers: {

                        size: 6, // Tamaño del marcador en el punto de la meta

                        strokeWidth: 1,

                        strokeColors: ['#000000'], // Borde negro

                        fillColors: ['#FF0000'], // Relleno rojo

                        hover: {

                            size: 8

                        },

                        shape: "diamond" // Mantener forma de diamante si se desea

                    },

                    tooltip: { // Tooltip específico

                        y: {

                            formatter: function(val) {

                                return val !== null ? formatNumberForApex(val) : undefined;

                            },

                            title: {

                                formatter: function(seriesName) {

                                    return seriesName + ':';

                                }

                            }

                        }

                    }

                }

            ],



            // --- CONFIGURACIÓN GENERAL DEL GRÁFICO ---

            chart: {

                height: 380,

                type: 'line', // Tipo de gráfico por defecto. Las series pueden anular esto.

                zoom: {

                    enabled: false

                },

                toolbar: {

                    show: true,

                    tools: {

                        download: false,

                        selection: false,

                        zoom: false,

                        zoomin: false,

                        zoomout: false,

                        pan: false,

                        reset: false

                    }

                },

                animations: {

                    enabled: true,

                    easing: 'easeinout',

                    speed: 800

                }

            },



            // --- COLORES PARA LAS SERIES (EN ORDEN DE DEFINICIÓN EN 'series') ---

            colors: [

                '#00E396', // Color para la Línea Base

                colorIndicadorJS, // Color para los Datos Históricos (la línea principal)

                '#FF0000' // Color para la Meta

            ],



            // --- ETIQUETAS DE DATOS (SOBRE LA GRÁFICA) ---

            dataLabels: {

                enabled: true

            },



            // --- TRAZO DE LÍNEAS (CONFIGURACIÓN GLOBAL) ---

            stroke: {

                curve: 'smooth', // Líneas suavizadas por defecto para todas las series 'line'

                width: 3 // Ancho por defecto para todas las series 'line'

                // Las series de Línea Base y Meta anulan esto con su propia config 'stroke'.

            },



            // --- MARCADORES (PUNTOS EN LA LÍNEA - CONFIGURACIÓN GLOBAL) ---

            markers: {

                size: 5, // Tamaño por defecto para los marcadores en series 'line'

                // Las series de Línea Base y Meta anulan esto con su config 'markers'.

                strokeColors: '#fff',

                strokeWidth: 2,

                hover: {

                    size: 7

                }

            },



            // --- TÍTULO ---

            title: {

                text: nombreIndicadorJS,

                align: 'left',

                style: {

                    fontSize: '16px',

                    fontWeight: 'bold'

                }

            },



            // --- EJE X ---

            xaxis: {

                categories: categoriasEjeXJS, // Años 2015-2030

                type: 'category',

                title: {

                    text: 'Año'

                },

                tooltip: {

                    enabled: false

                } // No mostrar tooltip del eje X si no aporta mucho

            },



            // --- EJE Y ---

            yaxis: {

                title: {

                    text: unidadMedidaJS

                },

                labels: {

                    formatter: function(val) {

                        return formatNumberForApex(val); // Formatear etiquetas del eje Y

                    }

                }

            },



            // --- LEYENDA ---

            legend: {

                position: 'top',

                horizontalAlign: 'right',

                floating: true,

                offsetY: -10,

                offsetX: -5,

                markers: { // Estilo de los marcadores en la leyenda

                    width: 12,

                    height: 12,

                    strokeWidth: 0, // Sin borde para el marcador de leyenda

                    radius: 2, // Para que los círculos se vean bien

                },

                itemMargin: {

                    horizontal: 10,

                    vertical: 0

                }

            },



            // --- TOOLTIP GENERAL (AL PASAR EL CURSOR) ---

            tooltip: {

                shared: true, // Un solo tooltip para todos los puntos en la misma X

                intersect: false, // El tooltip aparece al pasar cerca, no directamente encima

                x: {

                    format: 'yyyy' // Formato para el valor X en el tooltip (el año)

                },

                // El tooltip 'y' ahora se configura por serie para mayor control,

                // especialmente porque las series de Línea Base y Meta son puntos únicos.

                // Si se define un tooltip.y.formatter global, se aplicaría a todas las series

                // a menos que tengan su propio tooltip.y.formatter o tooltip.custom.

                // La configuración individual por serie que hicimos para Meta y Línea Base

                // (usando tooltip.y.formatter o tooltip.custom) es más precisa para esos casos.

                // Si quieres un tooltip general y que las series individuales lo anulen:

                // y: {

                //     formatter: function(value, { series, seriesIndex, w }) {

                //         return value !== null ? formatNumberForApex(value) : "N/D";

                //     }

                // }

            }

        };



        // Destruir gráfica anterior si existe (para evitar duplicados si el script se ejecuta múltiples veces sin recarga de página)

        var existingChart = ApexCharts.exec("grafica", 'destroy');



        var chart = new ApexCharts(document.querySelector("#grafica"), options);

        chart.render();

    });