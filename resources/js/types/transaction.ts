export interface Transaction {
    id: string;
    description: string;
    amount: string;
    direction: 'income' | 'expense';
    type: 'regular' | 'transfer' | 'investment' | 'loan';
    category_id?: string;
    bank_account_id?: string;
    payment_date: string;
    competence_month: string;
    status: 'pending' | 'paid' | 'cancelled';
    notes?: string;
    created_at: string;
    updated_at: string;
}