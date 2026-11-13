/**
 * Flowzen - Chart.js Visualizations
 * Initializes status distribution doughnut and weekly productivity trend charts
 * with the Nocturne Citron brand palette.
 */

function initDashboardCharts(statusCounts, weeklyTrend) {
    // 1. Task Status Distribution Doughnut Chart
    const statusCtx = document.getElementById('statusDoughnutChart');
    if (statusCtx && typeof Chart !== 'undefined') {
        const total = statusCounts.pending + statusCounts.in_progress + statusCounts.completed;

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Completed'],
                datasets: [{
                    data: total === 0 ? [1, 0, 0] : [statusCounts.pending, statusCounts.in_progress, statusCounts.completed],
                    backgroundColor: total === 0 ? ['#E5DDD2', '#E5DDD2', '#E5DDD2'] : [
                        '#B9A8D5', // Dusty Orchid
                        '#5CCFC0', // Mineral Teal
                        '#D9F36A'  // Electric Citron
                    ],
                    borderColor: '#FFFCF6',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'DM Sans', size: 12 },
                            padding: 16,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (total === 0) return ' No active tasks yet';
                                const count = context.raw || 0;
                                const pct = ((count / total) * 100).toFixed(1);
                                return ` ${context.label}: ${count} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Weekly Velocity Bar Chart
    const weeklyCtx = document.getElementById('weeklyVelocityChart');
    if (weeklyCtx && typeof Chart !== 'undefined') {
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: weeklyTrend.labels,
                datasets: [{
                    label: 'Tasks Completed',
                    data: weeklyTrend.data,
                    backgroundColor: '#5CCFC0',
                    borderRadius: 6,
                    borderSkipped: false,
                    hoverBackgroundColor: '#42B8A8'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { family: 'DM Sans', size: 11 }
                        },
                        grid: {
                            color: 'rgba(33, 27, 43, 0.05)'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'DM Sans', size: 11 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` Completed: ${ctx.raw} tasks`
                        }
                    }
                }
            }
        });
    }
}
