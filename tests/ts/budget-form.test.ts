import { describe, it, expect, beforeEach } from 'vitest';
import { calculateTotalLimits, initBudgetForm } from '../../resources/ts/modules/budget-form';

describe('calculateTotalLimits', () => {
  it('sums valid numeric strings', () => {
    const total = calculateTotalLimits(['100.50', '250.00', '49.50']);
    expect(total).toBe(400);
  });

  it('ignores blank and whitespace strings', () => {
    const total = calculateTotalLimits(['100.00', '', '   ', '50.00']);
    expect(total).toBe(150);
  });

  it('handles empty arrays', () => {
    const total = calculateTotalLimits([]);
    expect(total).toBe(0);
  });
});

describe('initBudgetForm DOM interaction', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <form data-budget-form data-currency="INR">
        <input data-budget-limit-input value="500.00" />
        <input data-budget-limit-input value="250.00" />
        <div data-budget-live-total>0.00</div>
      </form>
    `;
  });

  it('updates live total when input event fires', () => {
    initBudgetForm();

    const inputs = document.querySelectorAll<HTMLInputElement>('[data-budget-limit-input]');
    const liveTotalEl = document.querySelector<HTMLElement>('[data-budget-live-total]');

    // Change first input
    inputs[0]!.value = '1000.00';
    inputs[0]!.dispatchEvent(new Event('input'));

    expect(liveTotalEl?.textContent).toContain('1,250.00');
  });
});
