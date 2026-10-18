import { describe, it, expect } from 'vitest';
import { formatMoney } from '../../resources/ts/lib/money';

describe('formatMoney', () => {
    it('formats INR amounts using Indian numbering system', () => {
        const formatted = formatMoney(1234567.89, 'INR');
        // Clean non-breaking spaces for stable comparison
        const normalized = formatted.replace(/\u00a0/g, ' ');
        expect(normalized).toContain('12,34,567.89');
    });

    it('formats USD amounts using standard US grouping', () => {
        const formatted = formatMoney(1234567.89, 'USD');
        const normalized = formatted.replace(/\u00a0/g, ' ');
        expect(normalized).toContain('1,234,567.89');
    });

    it('formats string amounts correctly', () => {
        const formatted = formatMoney('500.50', 'INR');
        expect(formatted).toContain('500.50');
    });

    it('handles zero and edge values gracefully', () => {
        const formatted = formatMoney(0, 'INR');
        expect(formatted).toContain('0.00');
    });
});
