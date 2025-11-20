// Dashboard Penilai – Chart.js dari CDN

document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('kinerjaBulananChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    const dataTotal = [78, 38, 18, 68, 32, 12, 44, 96, 82, 15, 44, 88];
    const dataDiterima = [30, 98, 48, 68, 94, 36, 84, 60, 59, 38, 62, 94];
    const dataDitolak = [86, 40, 92, 20, 68, 84, 88, 78, 92, 20, 78, 65];

    const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
    gradientTotal.addColorStop(0, 'rgba(30, 64, 175, 0.25)');
    gradientTotal.addColorStop(1, 'rgba(30, 64, 175, 0.00)');

    const gradientDiterima = ctx.createLinearGradient(0, 0, 0, 260);
    gradientDiterima.addColorStop(0, 'rgba(28, 124, 84, 0.25)');
    gradientDiterima.addColorStop(1, 'rgba(28, 124, 84, 0.00)');

    const gradientDitolak = ctx.createLinearGradient(0, 0, 0, 260);
    gradientDitolak.addColorStop(0, 'rgba(182, 36, 28, 0.25)');
    gradientDitolak.addColorStop(1, 'rgba(182, 36, 28, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Laporan',
                    data: dataTotal,
                    borderColor: '#1E40AF',
                    backgroundColor: gradientTotal,
                    pointBackgroundColor: '#1E40AF',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0,
                    fill: true
                },
                {
                    label: 'Laporan Diterima',
                    data: dataDiterima,
                    borderColor: '#1C7C54',
                    backgroundColor: gradientDiterima,
                    pointBackgroundColor: '#1C7C54',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0,
                    fill: true,
                },
                {
                    label: 'Laporan Ditolak',
                    data: dataDitolak,
                    borderColor: '#B6241C',
                    backgroundColor: gradientDitolak,
                    pointBackgroundColor: '#B6241C',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        boxWidth: 10,
                        font: { size: 11 }
                    },
                },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: {
                    grid: { display: true, color: 'rgba(148, 163, 184, 0.2)' },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.25)' },
                    ticks: { stepSize: 20, font: { size: 11 } },
                },
            },
        },
    });
});
