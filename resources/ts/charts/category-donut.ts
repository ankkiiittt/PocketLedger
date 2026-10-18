import {
  Chart,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend,
} from 'chart.js';
import { CategoryBreakdownItem, CategoryBreakdownResponse } from '../types';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

export interface DonutChartData {
  labels: string[];
  data: number[];
  colors: string[];
}

/**
 * Group categories after top 6 into "Other". Pure function for testability.
 */
export function prepareDonutData(items: CategoryBreakdownItem[]): DonutChartData {
  if (items.length === 0) {
    return { labels: [], data: [], colors: [] };
  }

  const top6 = items.slice(0, 6);
  const remaining = items.slice(6);

  const labels = top6.map((item) => item.name);
  const data = top6.map((item) => parseFloat(item.amount) || 0);
  const colors = top6.map((item) => item.color);

  if (remaining.length > 0) {
    const otherSum = remaining.reduce((acc, item) => acc + (parseFloat(item.amount) || 0), 0);
    labels.push('Other');
    data.push(Math.round(otherSum * 100) / 100);
    colors.push('#5B6764');
  }

  return { labels, data, colors };
}

/**
 * Render doughnut chart on canvas.
 */
export function renderCategoryDonut(
  canvas: HTMLCanvasElement,
  response: CategoryBreakdownResponse,
  prefersReducedMotion = false
): Chart {
  const chartData = prepareDonutData(response.items);

  return new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          data: chartData.data,
          backgroundColor: chartData.colors,
          borderWidth: 1,
          borderColor: '#FFFFFF',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      animation: prefersReducedMotion ? false : { duration: 400 },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label(context) {
              const val = context.raw as number;
              return ` ${context.label}: ${val.toFixed(2)} ${response.currency}`;
            },
          },
        },
      },
    },
  });
}
