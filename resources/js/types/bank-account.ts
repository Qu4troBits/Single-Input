export interface BankAccount {
    id: string;
    name: string;
    bank_name: string;
    agency: string;
    account_number: string;
    type: 'checking' | 'savings';
    initial_balance: string;
    current_balance: string;
    active: boolean;
    notes?: string;
    created_at: string;
    updated_at: string;
}