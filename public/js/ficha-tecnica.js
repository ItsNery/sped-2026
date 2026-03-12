document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.fichaConfig === 'undefined') return;

    const config = window.fichaConfig;

    // =====================================================================
    // A. INICIALIZAR POPOVERS DE BOOTSTRAP (Botón de Info "?")
    // =====================================================================
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // =====================================================================
    // B. BOTÓN DE IMPRESIÓN
    // =====================================================================
    const btnImprimir = document.getElementById('btnImprimirFicha');
    if (btnImprimir) {
        btnImprimir.addEventListener('click', function () {
            window.print();
        });
    }

    // =====================================================================
    // C. GRÁFICA: VELOCÍMETRO (Gestión de Gobierno)
    // =====================================================================
    if (!config.esDatoLineaBase && document.querySelector("#gauge-ficha")) {
        var optionsFicha = {
            series: [Number(config.chartVal)],
            chart: {
                type: 'radialBar',
                height: 220,
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#f0f0f0",
                        strokeWidth: '97%'
                    },
                    dataLabels: {
                        name: { show: false },
                        value: { show: false }
                    }
                }
            },
            fill: {
                colors: [config.colorSemaforo]
            },
            stroke: {
                lineCap: 'round'
            }
        };
        var chartVelocimetro = new ApexCharts(document.querySelector("#gauge-ficha"), optionsFicha);
        chartVelocimetro.render();
    }

    // =====================================================================
    // D. GRÁFICA: EVOLUCIÓN HISTÓRICA (Líneas)
    // =====================================================================
    if (document.querySelector("#grafica-historica")) {
        const decimalesIndicador = config.idIndicador == 100 ? 6 : 2;
        const decimalesYAxis = config.idIndicador == 100 ? 6 : 0;

        // Función auxiliar para formatear números en la gráfica
        function formatNumber(value, decimalPlaces = decimalesIndicador) {
            if (value === null || value === undefined || isNaN(parseFloat(value))) return "N/D";
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: decimalPlaces,
                maximumFractionDigits: decimalPlaces
            });
        }

        var opcionesHistorico = {
            series: [{
                name: config.nombreSerieLineaBase,
                data: config.datosLineaBasePunto,
                type: 'line',
                zIndex: 10
            },
            {
                name: config.unidadMedida,
                data: config.datosParaGraficaPrincipal,
                type: 'line'
            },
            {
                name: 'Meta 2030',
                data: config.datosMetaPunto,
                type: 'line'
            }
            ],
            chart: {
                id: 'grafica-historica',
                height: 380,
                type: 'line',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    speed: 400
                }
            },
            colors: ['#00E396', config.colorIndicador, '#FF0000'],
            stroke: {
                curve: 'smooth',
                width: [2, 4, 2],
                dashArray: [5, 0, 5]
            },
            tooltip: {
                shared: false,
                intersect: true,
                theme: 'light',
                y: {
                    formatter: (val) => formatNumber(val) + ' ' + config.unidadMedida
                }
            },
            markers: {
                size: [6, 4, 7],
                hover: { sizeOffset: 3 }
            },
            xaxis: {
                categories: config.categoriasEjeX,
                title: { text: 'Año' }
            },
            yaxis: {
                labels: {
                    formatter: (val) => formatNumber(val, decimalesYAxis)
                },
                title: { text: config.unidadMedida }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [2],
                formatter: function (val) {
                    if (val === null || val === undefined || isNaN(parseFloat(val))) {
                        return "";
                    }
                    return parseFloat(val).toLocaleString('en-US', {
                        minimumFractionDigits: decimalesIndicador,
                        maximumFractionDigits: decimalesIndicador
                    });
                },
                offsetY: -5,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ["#FF0000"]
                },
                background: {
                    enabled: true,
                    foreColor: '#fff',
                    borderRadius: 4,
                    padding: 4,
                    borderColor: '#FF0000',
                }
            }
        };

        var chartHistorico = new ApexCharts(document.querySelector("#grafica-historica"), opcionesHistorico);
        chartHistorico.render();
    }
});
