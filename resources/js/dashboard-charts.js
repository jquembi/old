import Chart from 'chart.js/auto';

function registerDashboardCharts() {
    window.Alpine.data('dashboardCharts', (chartMunicipios, chartEstados, chartTipos, chartTendencia) => ({
        _themeObserver: null,
        _cachedData: null,

        init() {
            this._cachedData = { chartMunicipios, chartEstados, chartTipos, chartTendencia };
            this.$nextTick(() => {
                this.renderAllCharts(chartMunicipios, chartEstados, chartTipos, chartTendencia);
            });

            // Re-renderizar gráficos com cores e dimensões adaptadas ao alternar o tema
            this._themeObserver = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    if (mutation.attributeName === 'class' && this._cachedData) {
                        this.renderAllCharts(
                            this._cachedData.chartMunicipios,
                            this._cachedData.chartEstados,
                            this._cachedData.chartTipos,
                            this._cachedData.chartTendencia
                        );
                        break;
                    }
                }
            });

            this._themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });

            // Ajustar redimensionamento dos gráficos ao redimensionar a tela
            this._resizeHandler = () => {
                this.$root.querySelectorAll('canvas').forEach(canvas => {
                    Chart.getChart(canvas)?.resize();
                });
            };
            window.addEventListener('resize', this._resizeHandler);
        },

        destroy() {
            if (this._themeObserver) {
                this._themeObserver.disconnect();
            }
            if (this._resizeHandler) {
                window.removeEventListener('resize', this._resizeHandler);
            }
            this.$root.querySelectorAll('canvas').forEach(canvas => {
                Chart.getChart(canvas)?.destroy();
            });
        },

        renderAllCharts(municipiosData, estadosData, tiposData, tendenciaData) {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#a1a1aa' : '#71717a';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)';

            // 1. Gráfico de Municípios (Barra)
            const ctxMunicipios = this.$root.querySelector('#chartMunicipiosCanvas');
            if (ctxMunicipios) {
                Chart.getChart(ctxMunicipios)?.destroy();
                new Chart(ctxMunicipios, {
                    type: 'bar',
                    data: {
                        labels: municipiosData.labels,
                        datasets: [
                            {
                                label: 'Instrumentos',
                                data: municipiosData.instrumentos,
                                backgroundColor: '#dc2626',
                                borderRadius: 6,
                            },
                            {
                                label: 'Loteamentos',
                                data: municipiosData.loteamentos,
                                backgroundColor: '#eab308',
                                borderRadius: 6,
                            },
                            {
                                label: 'Projectos',
                                data: municipiosData.projectos,
                                backgroundColor: '#0d9488',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: textColor, boxWidth: 12, font: { size: 11, family: 'Inter' } }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            }
                        }
                    }
                });
            }

            // 2. Gráfico de Estados dos Instrumentos (Doughnut)
            const ctxEstados = this.$root.querySelector('#chartEstadosCanvas');
            if (ctxEstados) {
                Chart.getChart(ctxEstados)?.destroy();
                new Chart(ctxEstados, {
                    type: 'doughnut',
                    data: {
                        labels: estadosData.labels,
                        datasets: [{
                            data: estadosData.data,
                            backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, boxWidth: 10, font: { size: 10, family: 'Inter' } }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // 3. Gráfico de Tendência Mensal (Linha / Área suave)
            const ctxTendencia = this.$root.querySelector('#chartTendenciaCanvas');
            if (ctxTendencia) {
                Chart.getChart(ctxTendencia)?.destroy();
                new Chart(ctxTendencia, {
                    type: 'line',
                    data: {
                        labels: tendenciaData.labels,
                        datasets: [
                            {
                                label: 'Instrumentos',
                                data: tendenciaData.instrumentos,
                                borderColor: '#dc2626',
                                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Projectos',
                                data: tendenciaData.projectos,
                                borderColor: '#0d9488',
                                backgroundColor: 'rgba(13, 148, 136, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            }
                        }
                    }
                });
            }

            // 4. Gráfico de Tipos de Projectos (Polar/Doughnut)
            const ctxTipos = this.$root.querySelector('#chartTiposCanvas');
            if (ctxTipos) {
                Chart.getChart(ctxTipos)?.destroy();
                new Chart(ctxTipos, {
                    type: 'doughnut',
                    data: {
                        labels: tiposData.labels,
                        datasets: [{
                            data: tiposData.data,
                            backgroundColor: ['#0d9488', '#06b6d4', '#6366f1', '#a855f7', '#71717a'],
                            borderWidth: 0,
                            hoverOffset: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, boxWidth: 10, font: { size: 9, family: 'Inter' } }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        }
    }));
}

if (window.Alpine) {
    registerDashboardCharts();
} else {
    document.addEventListener('alpine:init', registerDashboardCharts, { once: true });
}
