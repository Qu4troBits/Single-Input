import React, { PropsWithChildren, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    Building2,
    Tag,
    ArrowLeftRight,
    TrendingUp,
    BarChart3,
    CreditCard,
    Menu,
    X,
    ChevronRight,
    LogOut,
    User,
} from 'lucide-react';

interface NavItem {
    label: string;
    href: string;
    icon: React.ReactNode;
    routeName: string;
}

const navItems: NavItem[] = [
    { label: 'Dashboard',       href: '/dashboard',      icon: <LayoutDashboard size={18} />, routeName: 'dashboard' },
    { label: 'Lançamentos',     href: '/transactions',   icon: <ArrowLeftRight size={18} />,  routeName: 'transactions.index' },
    { label: 'Contas Bancárias',href: '/bank-accounts',  icon: <Building2 size={18} />,       routeName: 'bank-accounts.index' },
    { label: 'Categorias',      href: '/categories',     icon: <Tag size={18} />,             routeName: 'categories.index' },
    { label: 'DRE',             href: '/dre',            icon: <TrendingUp size={18} />,      routeName: 'dre.index' },
    { label: 'Fluxo de Caixa',  href: '/cash-flow',      icon: <BarChart3 size={18} />,       routeName: 'cash-flow.index' },
    { label: 'Conciliação',     href: '/reconciliation', icon: <CreditCard size={18} />,      routeName: 'reconciliation.index' },
];

interface PageProps {
    auth?: {
        user?: {
            name: string;
            email: string;
        };
    };
    [key: string]: unknown;
}

export default function Layout({ children }: PropsWithChildren) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { url, props } = usePage<PageProps>();

    const auth = props.auth;

    const isActive = (href: string) => {
        return url.startsWith(href);
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-950 flex">

            {/* Overlay mobile */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-20 bg-black/50 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Sidebar */}
            <aside
                className={`
                    fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-900
                    border-r border-gray-200 dark:border-gray-800
                    transform transition-transform duration-200 ease-in-out
                    lg:translate-x-0 lg:static lg:inset-0
                    ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
                `}
            >
                {/* Logo */}
                <div className="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-800">
                    <Link href="/dashboard" className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                            <BarChart3 size={16} className="text-white" />
                        </div>
                        <span className="font-bold text-gray-900 dark:text-white text-lg">
                            FinanceApp
                        </span>
                    </Link>
                    <button
                        onClick={() => setSidebarOpen(false)}
                        className="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    >
                        <X size={20} />
                    </button>
                </div>

                {/* Nav */}
                <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    {navItems.map((item) => (
                        <Link
                            key={item.routeName}
                            href={item.href}
                            className={`
                                flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                transition-colors duration-150
                                ${isActive(item.href)
                                    ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white'
                                }
                            `}
                        >
                            <span className={isActive(item.href) ? 'text-indigo-600 dark:text-indigo-400' : ''}>
                                {item.icon}
                            </span>
                            {item.label}
                            {isActive(item.href) && (
                                <ChevronRight size={14} className="ml-auto text-indigo-400" />
                            )}
                        </Link>
                    ))}
                </nav>

                {/* User info */}
                <div className="border-t border-gray-200 dark:border-gray-800 p-4">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
                            <User size={14} className="text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {auth?.user?.name ?? 'Usuário'}
                            </p>
                            <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {auth?.user?.email ?? ''}
                            </p>
                        </div>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="text-gray-400 hover:text-red-500 transition-colors"
                            title="Sair"
                        >
                            <LogOut size={16} />
                        </Link>
                    </div>
                </div>
            </aside>

            {/* Main content */}
            <div className="flex-1 flex flex-col min-w-0">

                {/* Top bar */}
                <header className="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-10">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    >
                        <Menu size={20} />
                    </button>

                    {/* Breadcrumb slot — expansível no futuro */}
                    <div className="flex-1" />
                </header>

                {/* Page content */}
                <main className="flex-1 p-4 lg:p-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
