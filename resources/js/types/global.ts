export interface RouteFunction {
    (name: string, params?: Record<string, string | number | boolean>, absolute?: boolean): string;
}

declare global {
    function route(name: string, params?: Record<string, string | number | boolean>, absolute?: boolean): string;
    const route: RouteFunction;
}

export {};