import { describe, it, expect, beforeEach } from 'vitest';
import {
  filterCategoriesByType,
  filterSelectOptionsByType,
  CategoryItem,
} from '../../resources/ts/modules/transaction-form';

describe('filterCategoriesByType', () => {
  const categories: CategoryItem[] = [
    { id: 1, name: 'Groceries', type: 'expense' },
    { id: 2, name: 'Salary', type: 'income' },
    { id: 3, name: 'Rent', type: 'expense' },
    { id: 4, name: 'Freelance', type: 'income' },
  ];

  it('filters expense categories correctly', () => {
    const expenses = filterCategoriesByType(categories, 'expense');
    expect(expenses).toHaveLength(2);
    expect(expenses.map((c) => c.name)).toEqual(['Groceries', 'Rent']);
  });

  it('filters income categories correctly', () => {
    const incomes = filterCategoriesByType(categories, 'income');
    expect(incomes).toHaveLength(2);
    expect(incomes.map((c) => c.name)).toEqual(['Salary', 'Freelance']);
  });
});

describe('filterSelectOptionsByType', () => {
  let selectEl: HTMLSelectElement;

  beforeEach(() => {
    document.body.innerHTML = `
      <select id="category_id">
        <option value="" data-type="">Select a category</option>
        <option value="1" data-type="expense">Groceries</option>
        <option value="2" data-type="expense" selected>Rent</option>
        <option value="3" data-type="income">Salary</option>
        <option value="4" data-type="income">Freelance</option>
      </select>
    `;
    selectEl = document.getElementById('category_id') as HTMLSelectElement;
  });

  it('hides income options when expense is selected', () => {
    filterSelectOptionsByType(selectEl, 'expense');

    const expenseOpt = selectEl.querySelector('option[value="1"]') as HTMLOptionElement;
    const incomeOpt = selectEl.querySelector('option[value="3"]') as HTMLOptionElement;

    expect(expenseOpt.hidden).toBe(false);
    expect(incomeOpt.hidden).toBe(true);
    expect(incomeOpt.disabled).toBe(true);
  });

  it('switches selection to first matching option when current option is hidden', () => {
    // Currently "Rent" (expense) is selected
    expect(selectEl.value).toBe('2');

    // Switch to income
    filterSelectOptionsByType(selectEl, 'income');

    // Now first income option (Salary, value 3) should be selected
    expect(selectEl.value).toBe('3');
  });
});
