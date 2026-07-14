export interface ProjectionPeriod {
    start_date: string;
    end_date: string;
    type: 'monthly' | 'quarterly' | 'yearly';
    label: string;
}

export interface ProjectionItem {
    id: string;
    date: string;
    description: string;
    amount: string;
    type: 'revenue' | 'expense' | 'profit' | 'cash_flow' | 'balance_sheet';
    category_id?: string;
    category_name?: string;
    notes?: string;
    source?: 'historical' | 'manual' | 'formula';
}

export interface FinancialProjection {
    id?: string;
    period: ProjectionPeriod;
    type: 'revenue' | 'expense' | 'profit' | 'cash_flow' | 'balance_sheet';
    title: string;
    category_id?: string;
    scenario: 'base' | 'optimistic' | 'pessimistic' | 'custom';
    items: ProjectionItem[];
    total: string;
    generated_at: string;
    notes?: string;
}