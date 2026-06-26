export * from './global';
export * from './bank-account';
export * from './category';
export * from './transaction';
export * from './reports';
export * from './bank-reconciliation';
export * from './financial-projections';

export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    two_factor_enabled: boolean;
    created_at: string;
    updated_at: string;
}

export interface PageProps {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
    errors?: Record<string, string>;
}