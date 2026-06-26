export interface FinancialReportItem {
    code: string;
    description: string;
    amount: string;
    category_id?: string;
    category_name?: string;
    notes?: string;
}

export interface FinancialReport {
    period: {
        start_date: string;
        end_date: string;
    };
    title: string;
    items: FinancialReportItem[];
    total_revenue: string;
    total_expenses: string;
    gross_profit: string;
    operating_profit: string;
    net_profit: string;
    generated_at: string;
}