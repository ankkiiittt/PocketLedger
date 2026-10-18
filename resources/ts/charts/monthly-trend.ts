import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js';
import { MonthlyTrendItem, MonthlyTrendResponse } from '../types';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

export interface TrendChartData {
  labels: string[];
  incomeData: number[];
  expenseData: number[];
}

/**
 * Format YYYY-MM into short month label for chart x-axis.
 */
export function formatMonthLabel(ym: string): string {
  const parts = ym.split('-');
  const monthIdx = parseInt(parts[1] ?? '1', 10) - 1;
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return monthNames[monthIdx] ?? ym;
}

/**
 * Extract labels, income and expense data series from monthly trend items.
 */
export function prepareTrendData(months: MonthlyTrendItem[]): TrendChartData {
  const labels = months.map((m) => formatMonthLabel(m.month));
  const incomeData = months.map((m) => parseFloat(m.income) || 0);
  const expenseData = months.map((m) => parseFloat(m.expense) || 0);

  return { labels, incomeData, expenseData };
}

/**
 * Render grouped bar chart for monthly trend.
 */
export function renderMonthlyTrend(
  canvas: HTMLCanvasElement,
  response: MonthlyTrendResponse,
  prefersReducedMotion = false
): Chart {
  const chartData = prepareTrendData(response.months);

  return new Chart(canvas, {
    type: 'bar',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          label: 'Income',
          data: chartData.incomeData,
          backgroundColor: '#2E7D4F',
          borderRadius: 4,
        },
        {
          label: 'Expenses',
          data: chartData.expenseData,
          backgroundColor: '#B4432F',
          borderRadius: 4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      animation: prefersReducedMotion ? false : { duration: 400 },
      scales: {
        x: {
          grid: {
            display: false,
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback(val) {
              return typeof val === 'number' ? val.toLocaleString() : val;
            },
          },
        },
      },
      plugins: {
        legend: {
          position: 'top',
          align: 'end',
          labels: {
            boxWidth: 12,
            boxHeight: 12,
          },
        },
        tooltip: {
          callbacks: {
            label(context) {
              const val = context.raw as number;
              return ` ${context.dataset.label}: ${val.toFixed(2)} ${response.currency}`;
            },
          },
        },
      },
    },
  });
}
