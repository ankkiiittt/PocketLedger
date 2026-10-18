import { Chart } from 'chart.js';
import { renderCategoryDonut, prepareDonutData } from './category-donut';
import { renderMonthlyTrend } from './monthly-trend';
import { CategoryBreakdownResponse, MonthlyTrendResponse } from '../types';

export async function fetchChartJson<T>(url: string): Promise<T> {
  const response = await fetch(url, {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Failed to fetch chart data: ${response.status}`);
  }

  return response.json() as Promise<T>;
}

export function initCharts(): void {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Initialize donut charts (Dashboard & Reports)
  document.querySelectorAll<HTMLElement>('[data-chart="donut"]').forEach((container) => {
    setupDonutChart(container, prefersReducedMotion);
  });

  // Initialize trend charts (Reports)
  document.querySelectorAll<HTMLElement>('[data-chart="trend"]').forEach((container) => {
    setupTrendChart(container, prefersReducedMotion);
  });
}

function setupDonutChart(container: HTMLElement, prefersReducedMotion: boolean): void {
  const url = container.dataset.chartUrl;
  const canvas = container.querySelector<HTMLCanvasElement>('canvas[data-chart-canvas]');
  const loadingEl = container.querySelector<HTMLElement>('[data-chart-loading]');
  const errorEl = container.querySelector<HTMLElement>('[data-chart-error]');
  const retryBtn = container.querySelector<HTMLButtonElement>('[data-chart-retry]');
  const legendEl = container.querySelector<HTMLElement>('[data-donut-legend]');

  if (!url || !canvas) {
    return;
  }

  let activeChart: Chart | null = null;

  const loadData = async () => {
    if (loadingEl) {
      loadingEl.hidden = false;
    }
    if (errorEl) {
      errorEl.hidden = true;
    }
    canvas.hidden = true;

    try {
      const data = await fetchChartJson<CategoryBreakdownResponse>(url);
      if (loadingEl) {
        loadingEl.hidden = true;
      }
      canvas.hidden = false;

      if (activeChart) {
        activeChart.destroy();
      }
      activeChart = renderCategoryDonut(canvas, data, prefersReducedMotion);

      // Render legend if legend container exists
      if (legendEl) {
        renderDonutHtmlLegend(legendEl, data);
      }
    } catch {
      if (loadingEl) {
        loadingEl.hidden = true;
      }
      if (errorEl) {
        errorEl.hidden = false;
      }
      canvas.hidden = true;
    }
  };

  retryBtn?.addEventListener('click', () => {
    void loadData();
  });

  void loadData();
}

function renderDonutHtmlLegend(legendEl: HTMLElement, response: CategoryBreakdownResponse): void {
  const prepared = prepareDonutData(response.items);
  legendEl.innerHTML = '';

  if (prepared.labels.length === 0) {
    legendEl.innerHTML = '<p class="text-sm text-muted">No spending recorded for this month.</p>';
    return;
  }

  const list = document.createElement('div');
  list.className = 'donut-legend-list';

  prepared.labels.forEach((label, idx) => {
    const color = prepared.colors[idx] ?? '#5B6764';
    const amount = prepared.data[idx] ?? 0;

    const item = document.createElement('div');
    item.className = 'donut-legend-item';
    item.innerHTML = `
      <span class="category-color-dot" style="background-color: ${color};" aria-hidden="true"></span>
      <span class="donut-legend-label">${label}</span>
      <span class="donut-legend-val">${amount.toFixed(2)} ${response.currency}</span>
    `;
    list.appendChild(item);
  });

  legendEl.appendChild(list);
}

function setupTrendChart(container: HTMLElement, prefersReducedMotion: boolean): void {
  const url = container.dataset.chartUrl;
  const canvas = container.querySelector<HTMLCanvasElement>('canvas[data-chart-canvas]');
  const loadingEl = container.querySelector<HTMLElement>('[data-chart-loading]');
  const errorEl = container.querySelector<HTMLElement>('[data-chart-error]');
  const retryBtn = container.querySelector<HTMLButtonElement>('[data-chart-retry]');

  if (!url || !canvas) {
    return;
  }

  let activeChart: Chart | null = null;

  const loadData = async () => {
    if (loadingEl) {
      loadingEl.hidden = false;
    }
    if (errorEl) {
      errorEl.hidden = true;
    }
    canvas.hidden = true;

    try {
      const data = await fetchChartJson<MonthlyTrendResponse>(url);
      if (loadingEl) {
        loadingEl.hidden = true;
      }
      canvas.hidden = false;

      if (activeChart) {
        activeChart.destroy();
      }
      activeChart = renderMonthlyTrend(canvas, data, prefersReducedMotion);
    } catch {
      if (loadingEl) {
        loadingEl.hidden = true;
      }
      if (errorEl) {
        errorEl.hidden = false;
      }
      canvas.hidden = true;
    }
  };

  retryBtn?.addEventListener('click', () => {
    void loadData();
  });

  void loadData();
}
