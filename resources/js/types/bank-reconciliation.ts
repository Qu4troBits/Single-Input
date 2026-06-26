export interface ReconciliationItem {
    id: string;
    bank_account_id: string;
    date: string;
    description: string;
    amount: string;
    status: 'pending' | 'reconciled' | 'discrepancy' | 'adjusted';
    transaction_id?: string;
    bank_statement_id?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface ReconciliationSummary {
    pending_items: number;
    reconciled_items: number;
    discrepancy_items: number;
    total_credits: string;
    total_debits: string;
    expected_balance: string;
    actual_balance: string;
    generated_at: string;
}

export interface BankStatementItem {
    id: string;
    date: string;
    description: string;
    amount: string;
    type: 'credit' | 'debit';
    bank_reference?: string;
    notes?: string;
}