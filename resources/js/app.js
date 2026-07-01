import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

Alpine.start();

function createGradient(chart) {
    const gradient = chart.getContext('2d').createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(230, 106, 74, 0.28)');
    gradient.addColorStop(1, 'rgba(230, 106, 74, 0)');
    return gradient;
}

function renderSmoothLineChart(canvasId, labelSuffix, config = {}) {
    const canvas = document.getElementById(canvasId);

    if (!canvas) {
        return;
    }

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const values = JSON.parse(canvas.dataset.values || '[]');
    const min = config.min ?? 0;
    const max = config.max;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: labelSuffix,
                    data: values,
                    borderColor: '#e66a4a',
                    backgroundColor: createGradient(canvas),
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1a1a1a',
                    pointBorderColor: '#e66a4a',
                    pointBorderWidth: 2,
                    tension: 0.42,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f1f1f',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    titleColor: '#ffffff',
                    bodyColor: '#d4d4d4',
                    displayColors: false,
                },
            },
            scales: {
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: { color: '#737373', font: { size: 11 } },
                },
                y: {
                    beginAtZero: false,
                    min,
                    ...(max !== undefined ? { max } : {}),
                    border: { display: false },
                    grid: { color: 'rgba(255,255,255,0.035)', drawTicks: false },
                    afterBuildTicks: (scale) => {
                        if (max !== undefined && min === 70 && max >= 100) {
                            scale.ticks = [70, 80, 90, 100].map((value) => ({ value }));
                        }
                    },
                    ticks: {
                        precision: 0,
                        color: '#737373',
                        font: { size: 11 },
                        padding: 10,
                        callback: (value) => ([70, 80, 90, 100].includes(Number(value)) ? value : ''),
                    },
                },
            },
        },
    });
}

renderSmoothLineChart('monthly-restitution-chart', 'CID Restitusi');
renderSmoothLineChart('cid-sla-chart', 'SLA Achieved', { min: 70, max: 104 });
