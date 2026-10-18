export type CategoryType = 'income' | 'expense';

export type Frequency = 'daily' | 'weekly' | 'monthly' | 'yearly';

export type BudgetStatus = 'on_track' | 'nearing_limit' | 'over_budget';

export interface CategoryBreakdownItem {
    category_id: number;
    name: string;
    color: string;
    amount: string;
    percent: number;
}

export interface CategoryBreakdownResponse {
    month: string;
    currency: string;
    total: string;
    items: CategoryBreakdownItem[];
}

export interface MonthlyTrendItem {
    month: string;
    income: string;
    expense: string;
}

export interface MonthlyTrendResponse {
    currency: string;
    months: MonthlyTrendItem[];
}
