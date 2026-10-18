/**
 * Money formatting helper using Intl.NumberFormat
 */

export function formatMoney(amount: number | string, currency: string = 'INR'): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    const safeNum = Number.isFinite(num) ? num : 0;
    const locale = currency.toUpperCase() === 'INR' ? 'en-IN' : 'en-US';

    try {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency.toUpperCase(),
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(safeNum);
    } catch {
        // Fallback if currency code is unusual
        return `${currency} ${safeNum.toFixed(2)}`;
    }
}
