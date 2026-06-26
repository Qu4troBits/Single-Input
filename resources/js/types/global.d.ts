import { route as routeFn } from 'ziggy-js';

declare global {
    function route(name: string, params?: Record<string, string | number | boolean>, absolute?: boolean): string;
    const route: typeof routeFn;
}

export {};
