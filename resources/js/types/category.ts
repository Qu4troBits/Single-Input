export interface Category {
    id: string;
    name: string;
    type: 'income' | 'expense';
    parent_id?: string;
    color?: string;
    icon?: string;
    description?: string;
    active: boolean;
    created_at: string;
    updated_at: string;
}
export type CategoryType = 'revenue' | 'expense' | 'transfer';

export type CategoryStatus = 'active' | 'inactive' | 'archived';
