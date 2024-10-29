import Chart from 'chart.js/auto';
import 'chartjs-adapter-moment';
window.Chart = Chart

document.addEventListener('DOMContentLoaded', function () {
    // Get the JSON data from the script tag
    var pvsChartDataScript = document.getElementById('pvsChartData');
    if (pvsChartDataScript) {
        var pvsChartData = JSON.parse(pvsChartDataScript.textContent);

        if (typeof Chart !== 'undefined') {
            var ctx = document.getElementById('pvsChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: pvsChartData.dates,
                    datasets: [{
                        label: 'Daily Views',
                        data: pvsChartData.counts,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true
                    }]
                },
                options: {
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day'
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            console.error('Chart.js is not loaded.');
        }
    }  else {
        return;
    }
});