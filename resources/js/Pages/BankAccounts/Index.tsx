import React from 'react';
import { Head, Link } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { formatBRL } from '@/Utils/formatCurrency';

interface BankAccount {
    id: string;
    name: string;
    type: string;
    status: string;
    bank_code: string | null;
    agency: string | null;
    account_number: string | null;
    account_digit: string | null;
    description: string | null;
    balance: string;
    initial_balance: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    bankAccounts: BankAccount[];
}

export default function Index({ bankAccounts }: Props) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'inactive': return 'bg-yellow-100 text-yellow-800';
            case 'closed': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getTypeLabel = (type: string) => {
        switch (type) {
            case 'checking': return 'Conta Corrente';
            case 'savings': return 'Poupança';
            case 'investment': return 'Investimento';
            case 'credit_card': return 'Cartão de Crédito';
            case 'wallet': return 'Carteira';
            case 'other': return 'Outro';
            default: return type;
        }
    };

    return (
        <Layout>
            <Head title="Contas Bancárias" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Contas Bancárias</h1>
                        <p className="text-muted-foreground">
                            Gerencie suas contas bancárias e carteiras
                        </p>
                    </div>
                    <Link href={route('bank-accounts.create')}>
                        <Button>
                            Nova Conta
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Contas Bancárias</CardTitle>
                        <CardDescription>
                            Lista de todas as contas bancárias cadastradas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {bankAccounts.length === 0 ? (
                            <div className="text-center py-12">
                                <p className="text-muted-foreground">
                                    Nenhuma conta bancária cadastrada ainda.
                                </p>
                                <Link href={route('bank-accounts.create')} className="mt-4 inline-block">
                                    <Button variant="outline">
                                        Criar Primeira Conta
                                    </Button>
                                </Link>
                            </div>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nome</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Agência/Conta</TableHead>
                                        <TableHead>Saldo</TableHead>
                                        <TableHead>Criado em</TableHead>
                                        <TableHead className="text-right">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {bankAccounts.map((account) => (
                                        <TableRow key={account.id}>
                                            <TableCell className="font-medium">
                                                {account.name}
                                                {account.description && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {account.description}
                                                    </p>
                                                )}
                                            </TableCell>
                                            <TableCell>{getTypeLabel(account.type)}</TableCell>
                                            <TableCell>
                                                <Badge className={getStatusColor(account.status)}>
                                                    {account.status === 'active' ? 'Ativo' :
                                                     account.status === 'inactive' ? 'Inativo' : 'Fechado'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {account.bank_code && (
                                                    <span className="text-sm">
                                                        {account.bank_code} - {account.agency || 'N/A'} / {account.account_number || 'N/A'}{account.account_digit ? `-${account.account_digit}` : ''}
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatBRL(account.balance)}
                                            </TableCell>
                                            <TableCell>
                                                {new Date(account.created_at).toLocaleDateString('pt-BR')}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end space-x-2">
                                                    <Link href={route('bank-accounts.edit', account.id)}>
                                                        <Button variant="outline" size="sm">
                                                            Editar
                                                        </Button>
                                                    </Link>
                                                    <Link
                                                        href={route('bank-accounts.destroy', account.id)}
                                                        method="delete"
                                                        as="button"
                                                        className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
                                                        onClick={(e) => {
                                                            if (!confirm('Tem certeza que deseja excluir esta conta bancária?')) {
                                                                e.preventDefault();
                                                            }
                                                        }}
                                                    >
                                                        Excluir
                                                    </Link>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </Layout>
    );
}