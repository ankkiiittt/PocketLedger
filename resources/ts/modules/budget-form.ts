import { formatMoney } from '../lib/money';

/**
 * Calculate the sum of string amount values. Pure function for testability.
 */
export function calculateTotalLimits(values: string[]): number {
  return values.reduce((acc, val) => {
    const trimmed = val.trim();
    if (!trimmed) {
      return acc;
    }
    const num = parseFloat(trimmed);
    return isNaN(num) ? acc : acc + num;
  }, 0);
}

/**
 * Initialize live total computation on the budget form.
 */
export function initBudgetForm(): void {
  const form = document.querySelector<HTMLFormElement>('[data-budget-form]');
  if (!form) {
    return;
  }

  const liveTotalEl = form.querySelector<HTMLElement>('[data-budget-live-total]');
  const limitInputs = form.querySelectorAll<HTMLInputElement>('[data-budget-limit-input]');
  const currency = form.dataset.currency ?? 'INR';

  if (!liveTotalEl || limitInputs.length === 0) {
    return;
  }

  const updateLiveTotal = () => {
    const values = Array.from(limitInputs).map((input) => input.value);
    const total = calculateTotalLimits(values);
    liveTotalEl.textContent = formatMoney(total.toFixed(2), currency);
  };

  limitInputs.forEach((input) => {
    input.addEventListener('input', updateLiveTotal);
  });
}
