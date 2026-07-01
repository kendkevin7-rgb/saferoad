$(document).ready(function() {
    if (typeof trafficLabels !== 'undefined' && document.getElementById('trafficChart')) {
        new Chart(document.getElementById('trafficChart'), {
            type: 'line',
            data: {
                labels: trafficLabels.map(function(d) { return d.substring(5); }),
                datasets: [{
                    label: 'Speed Violations',
                    data: trafficData,
                    borderColor: '#ef476f',
                    backgroundColor: 'rgba(239,71,111,0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ef476f'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
