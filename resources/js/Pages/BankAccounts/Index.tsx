import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { formatBRL } from '@/Utils/formatCurrency';
import { BankAccountType, BankAccountStatus } from '@/types/bank-account';

interface BankAccount {
    id: string;
    name: string;
    type: BankAccountType;
    bankCode: string;
    bankName: string;
    agencyNumber: string;
    accountNumber: string;
    accountDigit: string | null;
    initialBalance: string;
    currentBalance: string;
    status: BankAccountStatus;
    description: string | null;
    color: string | null;
    icon: string | null;
    includeInDashboard: boolean;
    includeInReports: boolean;
    isDefault: boolean;
    createdAt: string;
    updatedAt: string;
}

interface Props extends PageProps {
    bankAccounts: BankAccount[];
    meta: {
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    filters: {
        type?: string;
        status?: string;
        include_in_dashboard?: boolean;
        include_in_reports?: boolean;
        is_default?: boolean;
    };
    bankAccountTypes: Array<{ value: string; label: string }>;
    bankAccountStatus: Array<{ value: string; label: string }>;
}

export default function BankAccountIndex({
    bankAccounts,
    meta,
    filters,
    bankAccountTypes,
    bankAccountStatus
}: Props) {
    const [localFilters, setLocalFilters] = useState({
        type: filters.type || '',
        status: filters.status || '',
        include_in_dashboard: filters.include_in_dashboard || false,
        include_in_reports: filters.include_in_reports || false,
        is_default: filters.is_default || false,
    });
    const [isFiltering, setIsFiltering] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (isFiltering) {
                router.get(route('bank-accounts.index'), localFilters, {
                    preserveState: true,
                    preserveScroll: true,
                });
                setIsFiltering(false);
            }
        }, 500);

        return () => clearTimeout(timer);
    }, [localFilters, isFiltering]);

    const handleFilterChange = (key: keyof typeof localFilters, value: any) => {
        setLocalFilters(prev => ({ ...prev, [key]: value }));
        setIsFiltering(true);
    };

    const getStatusBadgeVariant = (status: BankAccountStatus) => {
        switch (status) {
            case 'active':
                return 'success';
            case 'inactive':
                return 'secondary';
            case 'blocked':
                return 'destructive';
            default:
                return 'default';
        }
    };

    const getTypeLabel = (type: BankAccountType) => {
        return bankAccountTypes.find(t => t.value === type)?.label || type;
    };

    const getStatusLabel = (status: BankAccountStatus) => {
        return bankAccountStatus.find(s => s.value === status)?.label || status;
    };

    const getFullAccountNumber = (account: BankAccount) => {
        return account.accountNumber + (account.accountDigit ? '-' + account.accountDigit : '');
    };

    return (
        <AuthenticatedLayout>
            <Head title="Contas Bancárias" />

            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Contas Bancárias</h1>
                        <p className="text-muted-foreground">
                            Gerencie suas contas bancárias e acompanhe os saldos
                        </p>
                    </div>
                    <Link href={route('bank-accounts.create')}>
                        <Button>
                            Nova Conta
                        </Button>
                    </Link>
                </div>

                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>
                            Filtre as contas bancárias por tipo, status e outras características
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="type">Tipo</Label>
                                <Select
                                    value={localFilters.type}
                                    onValueChange={(value) => handleFilterChange('type', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os tipos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Todos os tipos</SelectItem>
                                        {bankAccountTypes.map((type) => (
                                            <SelectItem key={type.value} value={type.value}>
                                                {type.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select
                                    value={localFilters.status}
                                    onValueChange={(value) => handleFilterChange('status', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Todos os status</SelectItem>
                                        {bankAccountStatus.map((status) => (
                                            <SelectItem key={status.value} value={status.value}>
                                                {status.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="include_in_dashboard">Dashboard</Label>
                                <Select
                                    value={localFilters.include_in_dashboard ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('include_in_dashboard', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas no Dashboard</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="include_in_reports">Relatórios</Label>
                                <Select
                                    value={localFilters.include_in_reports ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('include_in_reports', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas em Relatórios</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="is_default">Padrão</Label>
                                <Select
                                    value={localFilters.is_default ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('is_default', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas Padrão</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Contas Bancárias</CardTitle>
                        <CardDescription>
                            Total de {meta.total} contas bancárias
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Banco</TableHead>
                                    <TableHead>Agência/Conta</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Saldo Atual</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {bankAccounts.map((account) => (
                                    <TableRow key={account.id}>
                                        <TableCell className="font-medium">
                                            <div className="flex items-center gap-2">
                                                {account.icon && (
                                                    <span className="text-lg">{account.icon}</span>
                                                )}
                                                {account.name}
                                                {account.isDefault && (
                                                    <Badge variant="outline" className="ml-2">
                                                        Padrão
                                                    </Badge>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-medium">{account.bankName}</span>
                                                <span className="text-sm text-muted-foreground">
                                                    Código: {account.bankCode}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span>Agência: {account.agencyNumber}</span>
                                                <span>Conta: {getFullAccountNumber(account)}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {getTypeLabel(account.type)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            <div className="flex flex-col">
                                                <span className={parseFloat(account.currentBalance) >= 0 ? 'text-green-600' : 'text-red-600'}>
                                                    {formatBRL(account.currentBalance)}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Inicial: {formatBRL(account.initialBalance)}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusBadgeVariant(account.status)}>
                                                {getStatusLabel(account.status)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('bank-accounts.show', { bank_account: account.id })}>
                                                    <Button variant="ghost" size="sm">
                                                        Ver
                                                    </Button>
                                                </Link>
                                                <Link href={route('bank-accounts.edit', { bank_account: account.id })}>
                                                    <Button variant="ghost" size="sm">
                                                        Editar
                                                    </Button>
                                                </Link>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {meta.total > 0 && (
                            <div className="flex items-center justify-between mt-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {meta.from} a {meta.to} de {meta.total} contas
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleFilterChange('type', localFilters.type)}
                                        disabled={meta.current_page === 1}
                                    >
                                        Anterior
                                    </Button>
                                    <span className="text-sm">
                                        Página {meta.current_page} de {meta.last_page}
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleFilterChange('type', localFilters.type)}
                                        disabled={meta.current_page === meta.last_page}
                                    >
                                        Próxima
                                    </Button>
                                </div>
                            </div>
                        )}

                        {bankAccounts.length === 0 && (
                            <div className="text-center py-12">
                                <div className="text-muted-foreground mb-4">
                                    Nenhuma conta bancária encontrada
                                </div>
                                <Link href={route('bank-accounts.create')}>
                                    <Button>
                                        Criar Primeira Conta
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
