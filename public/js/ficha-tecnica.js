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
         var chartGauge = echarts.init(gaugeElement, null, {
             renderer: config.pdfMode ? 'svg' : 'canvas'
         });
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

        // Calcula una regresión lineal únicamente con los datos históricos disponibles.
        function calcularTendencia(categorias, datos) {
            var puntos = [];

            categorias.forEach(function (categoria, indice) {
                var valor = datos[indice];
                var x = Number(categoria);
                var y = valor === null || valor === undefined || valor === '' ? NaN : Number(valor);

                if (Number.isFinite(x) && Number.isFinite(y)) {
                    puntos.push({ x: x, y: y, indice: indice });
                }
            });

            if (puntos.length < 2) return null;

            var n = puntos.length;
            var sumaX = puntos.reduce(function (total, punto) { return total + punto.x; }, 0);
            var sumaY = puntos.reduce(function (total, punto) { return total + punto.y; }, 0);
            var sumaXY = puntos.reduce(function (total, punto) { return total + punto.x * punto.y; }, 0);
            var sumaX2 = puntos.reduce(function (total, punto) { return total + punto.x * punto.x; }, 0);
            var denominador = n * sumaX2 - sumaX * sumaX;

            if (denominador === 0) return null;

            var pendiente = (n * sumaXY - sumaX * sumaY) / denominador;
            var intercepto = (sumaY - pendiente * sumaX) / n;
            var primeraObservacion = puntos[0].indice;
            var ultimaObservacion = puntos[puntos.length - 1].indice;

            return categorias.map(function (categoria, indice) {
                if (indice < primeraObservacion || indice > ultimaObservacion) return null;
                return pendiente * Number(categoria) + intercepto;
            });
        }

        var chartHistorico = echarts.init(document.getElementById('grafica-historica'), null, {
            renderer: config.pdfMode ? 'svg' : 'canvas'
        });
        var datosTendencia = calcularTendencia(config.categoriasEjeX, config.datosParaGraficaPrincipal);
        var seriesHistorico = [
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
                connectNulls: true,
                label: {
                    show: true,
                    position: 'top',
                    distance: 8,
                    formatter: function(params) {
                        return params.value === null || params.value === undefined
                            ? ''
                            : formatNumber(params.value);
                    },
                    color: config.colorIndicador,
                    fontSize: config.pdfMode ? 10 : 11,
                    fontWeight: 'bold',
                    backgroundColor: 'rgba(255, 255, 255, 0.94)',
                    borderColor: config.colorIndicador,
                    borderWidth: 1,
                    borderRadius: 4,
                    padding: config.pdfMode ? [2, 3] : [3, 5]
                }
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
                    position: 'top',
                    distance: 8,
                    formatter: function(params) { return params.value ? formatNumber(params.value) : ''; },
                    color: '#FF0000',
                    fontSize: config.pdfMode ? 10 : 11,
                    fontWeight: 'bold',
                    backgroundColor: 'rgba(255, 255, 255, 0.94)',
                    borderColor: '#FF0000',
                    borderWidth: 1,
                    borderRadius: 4,
                    padding: config.pdfMode ? [2, 3] : [3, 5]
                }
            }
        ];

        if (datosTendencia) {
            seriesHistorico.push({
                name: 'Tendencia',
                type: 'line',
                data: datosTendencia,
                symbol: 'none',
                lineStyle: { type: 'dashed', width: 2, color: '#F59E0B' },
                itemStyle: { color: '#F59E0B' },
                connectNulls: false,
                tooltip: { show: false }
            });
        }

        chartHistorico.setOption({
            animation: config.pdfMode ? false : true,
            textStyle: {
                fontFamily: 'Corra-Montserra-Regular, sans-serif'
            },
            tooltip: {
                trigger: 'axis',
                formatter: function(params) {
                    var res = params[0].axisValue;
                    var seriesVisibles = params.filter(function(p) { return p.seriesName !== 'Tendencia'; });

                    if (!seriesVisibles.length) return '';

                    seriesVisibles.forEach(function(p) {
                        if (p.value !== null && p.value !== undefined && !isNaN(p.value)) {
                            res += '<br/>' + p.marker + ' ' + p.seriesName + ': ' + formatNumber(p.value) + ' ' + config.unidadMedida;
                        }
                    });
                    return res;
                }
            },
            legend: {
                data: [config.nombreSerieLineaBase, config.unidadMedida, 'Meta 2030'].concat(datosTendencia ? ['Tendencia'] : []),
                bottom: 0,
                left: 'center',
                top: 'auto',
                orient: 'horizontal'
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: config.pdfMode ? 58 : 64,
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: config.categoriasEjeX,
                name: 'Año',
                nameLocation: 'middle',
                nameGap: config.pdfMode ? 24 : 30,
                axisLabel: {
                    interval: 0,
                    hideOverlap: false,
                    rotate: config.pdfMode ? 0 : (window.innerWidth < 768 ? 35 : 0),
                    fontSize: config.pdfMode ? 11 : 12,
                    margin: 12,
                    align: 'center',
                    verticalAlign: 'middle'
                }
            },
            yAxis: {
                type: 'value',
                name: config.unidadMedida,
                axisLabel: {
                    formatter: function(val) { return formatNumber(val); }
                }
            },
            series: seriesHistorico,
            labelLayout: {
                hideOverlap: false,
                moveOverlap: 'shiftY'
            }
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
