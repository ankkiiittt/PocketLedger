import { describe, it, expect } from 'vitest';
import { prepareDonutData } from '../../resources/ts/charts/category-donut';
import { prepareTrendData, formatMonthLabel } from '../../resources/ts/charts/monthly-trend';
import { CategoryBreakdownItem, MonthlyTrendItem } from '../../resources/ts/types';

describe('prepareDonutData', () => {
  it('groups items beyond the top 6 into Other', () => {
    const items: CategoryBreakdownItem[] = [
      { category_id: 1, name: 'Groceries', color: '#1F5F4D', amount: '100.00', percent: 25 },
      { category_id: 2, name: 'Rent', color: '#B4432F', amount: '90.00', percent: 22.5 },
      { category_id: 3, name: 'Utilities', color: '#2F5F8A', amount: '80.00', percent: 20 },
      { category_id: 4, name: 'Dining', color: '#B7791F', amount: '50.00', percent: 12.5 },
      { category_id: 5, name: 'Transport', color: '#6B4E8F', amount: '30.00', percent: 7.5 },
      { category_id: 6, name: 'Health', color: '#3D8B8B', amount: '20.00', percent: 5 },
      // 7th and 8th items should be grouped into Other
      { category_id: 7, name: 'Shopping', color: '#8A5A44', amount: '15.00', percent: 3.75 },
      { category_id: 8, name: 'Entertainment', color: '#6F7D3A', amount: '15.00', percent: 3.75 },
    ];

    const result = prepareDonutData(items);

    expect(result.labels).toHaveLength(7);
    expect(result.labels[6]).toBe('Other');
    expect(result.data[6]).toBe(30.00); // 15 + 15
    expect(result.colors[6]).toBe('#5B6764');
  });

  it('keeps all items as-is when there are 6 or fewer items', () => {
    const items: CategoryBreakdownItem[] = [
      { category_id: 1, name: 'Groceries', color: '#1F5F4D', amount: '100.00', percent: 50 },
      { category_id: 2, name: 'Rent', color: '#B4432F', amount: '100.00', percent: 50 },
    ];

    const result = prepareDonutData(items);

    expect(result.labels).toEqual(['Groceries', 'Rent']);
    expect(result.data).toEqual([100, 100]);
    expect(result.colors).toEqual(['#1F5F4D', '#B4432F']);
  });

  it('handles empty item list', () => {
    const result = prepareDonutData([]);
    expect(result.labels).toEqual([]);
    expect(result.data).toEqual([]);
    expect(result.colors).toEqual([]);
  });
});

describe('prepareTrendData and formatMonthLabel', () => {
  it('formats month labels correctly', () => {
    expect(formatMonthLabel('2026-05')).toBe('May');
    expect(formatMonthLabel('2026-10')).toBe('Oct');
    expect(formatMonthLabel('2026-01')).toBe('Jan');
  });

  it('extracts income and expense arrays matching months', () => {
    const months: MonthlyTrendItem[] = [
      { month: '2026-05', income: '5000.00', expense: '3200.00' },
      { month: '2026-06', income: '6000.00', expense: '4000.00' },
    ];

    const result = prepareTrendData(months);

    expect(result.labels).toEqual(['May', 'Jun']);
    expect(result.incomeData).toEqual([5000, 6000]);
    expect(result.expenseData).toEqual([3200, 4000]);
  });
});
