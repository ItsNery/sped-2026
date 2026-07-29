console.log('ficha-tecnica.js script loaded! readyState =', document.readyState);

function initFicha() {
    console.log('initFicha executed!');
    if (typeof window.fichaConfig === 'undefined') {
        console.warn('window.fichaConfig is undefined, aborting initialization.');
        return;
    }

    const config = window.fichaConfig;

    // =====================================================================
    // A. INICIALIZAR POPOVERS DE BOOTSTRAP (Botón de Info "?")
    // =====================================================================
    if (typeof bootstrap !== 'undefined') {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    try {
        // =====================================================================
        // B. GRÁFICA: VELOCÍMETRO (Gestión de Gobierno) - ECharts Gauge
        // =====================================================================
        if (!config.esDatoLineaBase && document.querySelector("#gauge-ficha")) {
        var gaugeElement = document.getElementById('gauge-ficha');
        var chartGauge = echarts.init(gaugeElement);
        chartGauge.setOption({
            animation: config.pdfMode ? false : true,
            series: [{
                type: 'gauge',
                startAngle: 180,
                endAngle: 0,
                min: 0,
                max: 100,
                progress: {
                    show: true,
                    width: 15,
                    roundCap: true,
                    itemStyle: { color: config.colorSemaforo }
                },
                axisLine: {
                    lineStyle: { width: 15, color: [[1, '#f0f0f0']] }
                },
                axisTick: { show: false },
                splitLine: { show: false },
                axisLabel: { show: false },
                pointer: { show: false },
                detail: { show: false },
                data: [{ value: Number(config.chartVal) }]
            }]
        });
        chartGauge.resize();
        requestAnimationFrame(function () {
            chartGauge.resize();
        });
        window.addEventListener('resize', function () {
            chartGauge.resize();
        });
        }

        // =====================================================================
        // C. GRÁFICA: EVOLUCIÓN HISTÓRICA (Líneas) - ECharts
        // =====================================================================
        if (document.querySelector("#grafica-historica")) {
        const decimalesIndicador = config.idIndicador == 100 ? 6 : 2;

        // Función auxiliar para formatear números en la gráfica
        function formatNumber(value, decimalPlaces = decimalesIndicador) {
            if (value === null || value === undefined || isNaN(parseFloat(value))) return "N/D";
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: decimalPlaces,
                maximumFractionDigits: decimalPlaces
            });
        }

        var chartHistorico = echarts.init(document.getElementById('grafica-historica'));
        chartHistorico.setOption({
            animation: config.pdfMode ? false : true,
            tooltip: {
                trigger: 'axis',
                formatter: function(params) {
                    var res = params[0].axisValue;
                    params.forEach(function(p) {
                        if (p.value !== null && p.value !== undefined && !isNaN(p.value)) {
                            res += '<br/>' + p.marker + ' ' + p.seriesName + ': ' + formatNumber(p.value) + ' ' + config.unidadMedida;
                        }
                    });
                    return res;
                }
            },
            legend: {
                data: [config.nombreSerieLineaBase, config.unidadMedida, 'Meta 2030'],
                top: 'top'
            },
            xAxis: {
                type: 'category',
                data: config.categoriasEjeX,
                name: 'Año'
            },
            yAxis: {
                type: 'value',
                name: config.unidadMedida,
                axisLabel: {
                    formatter: function(val) { return formatNumber(val); }
                }
            },
            series: [
                {
                    name: config.nombreSerieLineaBase,
                    type: 'line',
                    data: config.datosLineaBasePunto,
                    lineStyle: { type: 'dashed', width: 2 },
                    showSymbol: true,
                    symbol: 'circle',
                    symbolSize: 6,
                    itemStyle: { color: '#00E396' },
                    connectNulls: true
                },
                {
                    name: config.unidadMedida,
                    type: 'line',
                    data: config.datosParaGraficaPrincipal,
                    lineStyle: { width: 4, color: config.colorIndicador },
                    showSymbol: true,
                    symbol: 'circle',
                    symbolSize: 4,
                    itemStyle: { color: config.colorIndicador },
                    smooth: true,
                    connectNulls: true
                },
                {
                    name: 'Meta 2030',
                    type: 'line',
                    data: config.datosMetaPunto,
                    lineStyle: { type: 'dashed', width: 2 },
                    showSymbol: true,
                    symbol: 'circle',
                    symbolSize: 7,
                    itemStyle: { color: '#FF0000' },
                    connectNulls: true,
                    label: {
                        show: true,
                        formatter: function(params) { return params.value ? formatNumber(params.value) : ''; },
                        color: '#FF0000',
                        fontWeight: 'bold'
                    }
                }
            ]
        });
        chartHistorico.resize();
        }
    } catch (error) {
        console.error('No fue posible inicializar las gráficas de la ficha:', error);
    } finally {
        const markPdfReady = function () {
            ['gauge-ficha', 'grafica-historica'].forEach(function (id) {
                const element = document.getElementById(id);
                if (element && typeof echarts !== 'undefined') {
                    const chart = echarts.getInstanceByDom(element);
                    if (chart) chart.resize();
                }
            });

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    window.pdfReady = true;
                });
            });
        };

        if (document.fonts && document.fonts.ready) {
            Promise.race([
                document.fonts.ready,
                new Promise(function (resolve) { setTimeout(resolve, 500); })
            ]).then(markPdfReady);
        } else {
            markPdfReady();
        }
    }
}

// Inicialización segura evitando problemas de race condition con DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFicha);
} else {
    initFicha();
}
