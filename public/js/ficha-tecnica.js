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
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // =====================================================================
    // B. BOTÓN DE IMPRESIÓN CON GENERACIÓN DE PDF (USANDO BLOB URL)
    // =====================================================================
    const btnImprimir = document.getElementById('btnImprimirFicha');
    if (btnImprimir) {
        console.log('btnImprimir element found, registering click listener.');
        
        // Botón de PDF Vectorial (impresión nativa del navegador)
        const btnImprimirNativo = document.getElementById('btnImprimirNativo');
        if (btnImprimirNativo) {
            btnImprimirNativo.addEventListener('click', function () {
                window.print();
            });
        }

        btnImprimir.addEventListener('click', function () {
            console.log('btnImprimir clicked!');
            if (typeof html2pdf === 'undefined') {
                console.warn('La librería html2pdf.js no está cargada. Usando impresión nativa.');
                window.print();
                return;
            }

            const element = document.getElementById('imprimir');
            if (!element) {
                console.error('Elemento #imprimir no encontrado!');
                return;
            }
            
            // Agregar clase temporal para aplicar estilos específicos de PDF
            element.classList.add('generating-pdf');

            // Obtener el nombre del indicador para el archivo PDF
            let titleText = 'ficha-tecnica';
            const h1Element = element.querySelector('h1');
            if (h1Element) {
                titleText = h1Element.innerText
                    .trim()
                    .toLowerCase()
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "") // Quitar acentos
                    .replace(/[^a-z0-9]+/g, '-');   // Reemplazar espacios y caracteres raros con guion
            }

            const opt = {
                margin:       [5, 5, 5, 5],
                filename:     `ficha-tecnica-${titleText}.pdf`,
                image:        { type: 'jpeg', quality: 0.95 },
                html2canvas:  { 
                    scale: 3, 
                    useCORS: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['css', 'legacy'] }
            };

            console.log('Starting html2pdf generation with options:', opt);
            // Generar el PDF como Blob y descargar mediante elemento <a> temporal
            html2pdf().set(opt).from(element).toPdf().output('blob').then(function(blob) {
                console.log('PDF generado exitosamente como Blob. Iniciando descarga...');
                const blobURL = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = blobURL;
                a.download = `ficha-tecnica-${titleText}.pdf`;
                document.body.appendChild(a);
                a.click();
                
                // Limpieza de memoria
                setTimeout(function() {
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobURL);
                    console.log('Descarga iniciada y recursos de Blob liberados.');
                }, 150);

                element.classList.remove('generating-pdf');
            }).catch(function(err) {
                console.error('Error al generar PDF:', err);
                element.classList.remove('generating-pdf');
            });
        });
    }

    // =====================================================================
    // C. GRÁFICA: VELOCÍMETRO (Gestión de Gobierno) - ECharts Gauge
    // =====================================================================
    if (!config.esDatoLineaBase && document.querySelector("#gauge-ficha")) {
        var chartGauge = echarts.init(document.getElementById('gauge-ficha'));
        chartGauge.setOption({
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
    }

    // =====================================================================
    // D. GRÁFICA: EVOLUCIÓN HISTÓRICA (Líneas) - ECharts
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
                    symbolSize: 6,
                    itemStyle: { color: '#00E396' },
                    connectNulls: true
                },
                {
                    name: config.unidadMedida,
                    type: 'line',
                    data: config.datosParaGraficaPrincipal,
                    lineStyle: { width: 4, color: config.colorIndicador },
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
}

// Inicialización segura evitando problemas de race condition con DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFicha);
} else {
    initFicha();
}
