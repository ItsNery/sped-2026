(function () {
    function renderMiniChart(element) {
        if (element.dataset.chartReady || typeof echarts === 'undefined' || element.clientWidth === 0) {
            return;
        }

        var payload = JSON.parse(element.dataset.indicatorMiniChart);
        var categories = payload.datos.map(function (item) { return item.periodo; });
        var values = payload.datos.map(function (item) { return item.valor; });
        var color = payload.color || '#6c757d';
        var series = {
            type: 'line',
            silent: true,
            data: values,
            smooth: values.length > 2,
            symbol: values.length === 1 ? 'circle' : 'none',
            symbolSize: 7,
            lineStyle: { width: 2, color: color },
            itemStyle: { color: color },
            areaStyle: { color: color + '18' }
        };

        if (payload.meta !== null) {
            series.markLine = {
                silent: true,
                symbol: 'none',
                label: { show: false },
                lineStyle: { width: 1, type: 'dashed', color: '#8d918e' },
                data: [{ yAxis: payload.meta }]
            };
        }

        var chart = echarts.init(element, null, { renderer: 'svg' });
        chart.setOption({
            animation: false,
            grid: { top: 8, right: 3, bottom: 5, left: 3 },
            tooltip: { show: false },
            xAxis: { type: 'category', show: false, data: categories, boundaryGap: values.length === 1 },
            yAxis: { type: 'value', show: false, scale: true },
            series: [series]
        });

        element.dataset.chartReady = 'true';
        element.indicatorChart = chart;
    }

    window.renderIndicatorMiniCharts = function (container) {
        window.requestAnimationFrame(function () {
            container.querySelectorAll('[data-indicator-mini-chart]').forEach(function (element) {
                renderMiniChart(element);
                if (element.indicatorChart) {
                    element.indicatorChart.resize();
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        var elements = document.querySelectorAll('[data-indicator-mini-chart]');

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        renderMiniChart(entry.target);
                        if (entry.target.dataset.chartReady) {
                            observer.unobserve(entry.target);
                        }
                    }
                });
            }, { rootMargin: '150px' });

            elements.forEach(function (element) { observer.observe(element); });
        } else {
            elements.forEach(renderMiniChart);
        }

        window.addEventListener('resize', function () {
            document.querySelectorAll('[data-indicator-mini-chart][data-chart-ready]').forEach(function (element) {
                element.indicatorChart.resize();
            });
        });
    });
})();
