export type CategoryType = 'income' | 'expense';

export interface CategoryBreakdownItem {
  category_id: number;
  name: string;
  color: string;
  amount: string;
  percent: number;
  limit?: string | null;
  variance?: string | null;
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
