/**
 * Daily Expense Management System
 * Chart.js Visualizations Controller
 */

'use strict';

let categoryChartInstance = null;
let dailyCashflowChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  initCategoryDoughnut();
  initDailyCashflowBar();
});

/**
 * 1. Initialize Category Expense Doughnut Chart
 */
function initCategoryDoughnut() {
  const canvas = document.getElementById('categoryDoughnutChart');
  if (!canvas || typeof Chart === 'undefined') return;

  try {
    const rawLabels = canvas.getAttribute('data-labels');
    const rawValues = canvas.getAttribute('data-values');
    const currency = canvas.getAttribute('data-currency') || '₹';

    if (!rawLabels || !rawValues) return;

    const labels = JSON.parse(rawLabels);
    const dataValues = JSON.parse(rawValues);

    if (labels.length === 0) return;

    const ctx = canvas.getContext('2d');
    const palette = [
      '#ef4444', '#f97316', '#f59e0b', '#10b981', '#06b6d4',
      '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899', '#14b8a6'
    ];

    categoryChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: dataValues,
          backgroundColor: palette.slice(0, labels.length),
          borderWidth: 2,
          borderColor: document.body.classList.contains('dark-mode') ? '#111827' : '#ffffff',
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              boxWidth: 12,
              padding: 14,
              font: {
                family: "'Plus Jakarta Sans', sans-serif",
                size: 12
              },
              color: document.body.classList.contains('dark-mode') ? '#cbd5e1' : '#475569'
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const val = Number(context.raw || 0);
                const total = context.dataset.data.reduce((a, b) => a + Number(b), 0);
                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                return ` ${label}: ${currency} ${val.toLocaleString(undefined, {minimumFractionDigits: 2})} (${pct}%)`;
              }
            }
          }
        },
        cutout: '68%'
      }
    });
  } catch (err) {
    console.error('Failed to initialize category chart:', err);
  }
}

/**
 * 2. Initialize Daily Cashflow (Income vs Expense) Bar Chart
 */
function initDailyCashflowBar() {
  const canvas = document.getElementById('dailyCashflowBarChart');
  if (!canvas || typeof Chart === 'undefined') return;

  try {
    const rawDays = canvas.getAttribute('data-days');
    const rawIncome = canvas.getAttribute('data-income');
    const rawExpense = canvas.getAttribute('data-expense');
    const currency = canvas.getAttribute('data-currency') || '₹';

    if (!rawDays) return;

    const labels = JSON.parse(rawDays);
    const incomeData = JSON.parse(rawIncome);
    const expenseData = JSON.parse(rawExpense);

    const ctx = canvas.getContext('2d');
    const isDark = document.body.classList.contains('dark-mode');

    dailyCashflowChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Income',
            data: incomeData,
            backgroundColor: '#16a34a',
            borderRadius: 4,
            barPercentage: 0.7,
            categoryPercentage: 0.6
          },
          {
            label: 'Expense',
            data: expenseData,
            backgroundColor: '#dc2626',
            borderRadius: 4,
            barPercentage: 0.7,
            categoryPercentage: 0.6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                family: "'Plus Jakarta Sans', sans-serif",
                size: 10
              },
              color: isDark ? '#94a3b8' : '#64748b',
              maxRotation: 0,
              autoSkip: true,
              maxTicksLimit: 12
            }
          },
          y: {
            grid: {
              color: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)'
            },
            ticks: {
              font: {
                family: "'JetBrains Mono', monospace",
                size: 11
              },
              color: isDark ? '#94a3b8' : '#64748b',
              callback: function(val) {
                if (val >= 1000) {
                  return currency + (val / 1000).toFixed(0) + 'k';
                }
                return currency + val;
              }
            }
          }
        },
        plugins: {
          legend: {
            position: 'top',
            align: 'end',
            labels: {
              boxWidth: 10,
              padding: 10,
              font: {
                family: "'Plus Jakarta Sans', sans-serif",
                size: 11
              },
              color: isDark ? '#cbd5e1' : '#475569'
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.dataset.label || '';
                const val = Number(context.raw || 0);
                return ` ${label}: ${currency} ${val.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
              }
            }
          }
        }
      }
    });
  } catch (err) {
    console.error('Failed to initialize cashflow bar chart:', err);
  }
}

/**
 * 3. Update Chart Theme Colors when Dark/Light toggle is clicked
 */
window.updateChartsTheme = function(isDark) {
  const fontColor = isDark ? '#cbd5e1' : '#475569';
  const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';

  if (categoryChartInstance) {
    categoryChartInstance.data.datasets[0].borderColor = isDark ? '#111827' : '#ffffff';
    categoryChartInstance.options.plugins.legend.labels.color = fontColor;
    categoryChartInstance.update();
  }

  if (dailyCashflowChartInstance) {
    dailyCashflowChartInstance.options.scales.x.ticks.color = fontColor;
    dailyCashflowChartInstance.options.scales.y.ticks.color = fontColor;
    dailyCashflowChartInstance.options.scales.y.grid.color = gridColor;
    dailyCashflowChartInstance.options.plugins.legend.labels.color = fontColor;
    dailyCashflowChartInstance.update();
  }
};
