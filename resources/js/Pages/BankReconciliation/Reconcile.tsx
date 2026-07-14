import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Link } from '@inertiajs/react';
import { BankAccount, ReconciliationItem, Transaction } from '@/types';
import { useState } from 'react';
import { formatBRL } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { number } from 'zod';

interface BankReconciliationReconcileProps extends PageProps {
    bank_account: BankAccount;
    pending_items: ReconciliationItem[];
    transactions: Transaction[];
}

interface ReconciliationFormItem {
    id: string;
    description: string;
    amount: string;
    date: string;
    status: 'pending' | 'reconciled' | 'discrepancy' | 'adjusted';
    transaction_id?: string;
    bank_statement_id?: string;
    notes?: string;
}

export default function Reconcile({ auth, bank_account, pending_items, transactions }: BankReconciliationReconcileProps) {
    const [selectedTransaction, setSelectedTransaction] = useState<string>('');
    const [selectedItem, setSelectedItem] = useState<string>('');

    const initialItems: ReconciliationFormItem[] = pending_items.map(item => ({
        id: item.id,
        description: item.description,
        amount: item.amount,
        date: item.date.split('T')[0],
        status: item.status as 'pending' | 'reconciled' | 'discrepancy' | 'adjusted',
        transaction_id: item.transaction_id || undefined,
        bank_statement_id: item.bank_statement_id || undefined,
        notes: item.notes || undefined,
    }));

    const { data, setData, post, processing, errors } = useForm({
        reconciliation_date: new Date().toISOString().split('T')[0],
        notes: '',
        items: initialItems,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('bank-reconciliation.reconcile', { bank_account: bank_account.id }));
    };

    const updateItemStatus = (itemId: string, newStatus: ReconciliationFormItem['status']) => {
        const newItems = data.items.map(item =>
            item.id === itemId ? { ...item, status: newStatus } : item
        );
        setData('items', newItems);
    };

    const associateTransaction = () => {
        if (!selectedTransaction || !selectedItem) return;

        const newItems = data.items.map(item =>
            item.id === selectedItem
                ? { ...item, transaction_id: selectedTransaction, status: 'reconciled' as const }
                : item
        );
        setData('items', newItems);
        setSelectedTransaction('');
        setSelectedItem('');
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'reconciled': return 'bg-green-100 text-green-800';
            case 'discrepancy': return 'bg-red-100 text-red-800';
            case 'adjusted': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusText = (status: string) => {
        switch (status) {
            case 'pending': return 'Pendente';
            case 'reconciled': return 'Conciliado';
            case 'discrepancy': return 'Divergência';
            case 'adjusted': return 'Ajustado';
            default: return 'Desconhecido';
        }
    };

    return (
        <AuthenticatedLayout
        >
            <Head title={`Conciliação Manual: ${bank_account.name}`} />
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">
                        Conciliação Manual
                    </h2>
                    <p className="text-muted-foreground">
                        Conta: {bank_account.name} • Banco: {bank_account.bank_name}
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href={route('bank-reconciliation.show', { bank_account: bank_account.id })}>
                            Voltar
                        </Link>
                    </Button>
                </div>
            </div>
            <div className="max-w-6xl mx-auto">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Painel de Associação */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Associar Transações</CardTitle>
                                <CardDescription>
                                    Associe itens pendentes com transações do sistema
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="select_item">Selecionar Item Pendente</Label>
                                            <Select value={selectedItem} onValueChange={setSelectedItem}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Selecione um item" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {data.items
                                                        .filter(item => item.status === 'pending')
                                                        .map(item => (
                                                            <SelectItem key={item.id} value={item.id}>
                                                                {item.description} - {formatBRL(item.amount)}
                                                            </SelectItem>
                                                        ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="select_transaction">Selecionar Transação</Label>
                                            <Select value={selectedTransaction} onValueChange={setSelectedTransaction}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Selecione uma transação" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {transactions.map(transaction => (
                                                        <SelectItem key={transaction.id} value={transaction.id}>
                                                            {transaction.description} - {formatBRL(transaction.amount)}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <Button
                                        type="button"
                                        onClick={associateTransaction}
                                        disabled={!selectedItem || !selectedTransaction}
                                    >
                                        Associar Transação
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Lista de Itens */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Itens para Conciliação</CardTitle>
                                <CardDescription>
                                    Gerencie o status de cada item
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {data.items.map((item) => (
                                        <Card key={item.id} className="border">
                                            <CardContent className="pt-6">
                                                <div className="flex items-start justify-between">
                                                    <div className="space-y-1">
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-medium">{item.description}</span>
                                                            <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(item.status)}`}>
                                                                {getStatusText(item.status)}
                                                            </span>
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            Data: {formatDate(item.date)} • Valor: {formatBRL(item.amount)}
                                                        </div>
                                                        {item.transaction_id && (
                                                            <div className="text-sm text-blue-600">
                                                                Associada à transação: {item.transaction_id}
                                                            </div>
                                                        )}
                                                        {item.notes && (
                                                            <div className="text-sm text-muted-foreground">
                                                                Observações: {item.notes}
                                                            </div>
                                                        )}
                                                    </div>

                                                    <div className="flex gap-2">
                                                        <Select
                                                            value={item.status}
                                                            onValueChange={(value) => updateItemStatus(
                                                                    item.id,
                                                                    value as ReconciliationFormItem['status']
                                                                )}
                                                        >
                                                            <SelectTrigger className="w-32">
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="pending">Pendente</SelectItem>
                                                                <SelectItem value="reconciled">Conciliado</SelectItem>
                                                                <SelectItem value="discrepancy">Divergência</SelectItem>
                                                                <SelectItem value="adjusted">Ajustado</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Formulário de Conciliação */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Conciliação</CardTitle>
                                <CardDescription>
                                    Finalize o processo de conciliação
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="reconciliation_date">Data da Conciliação</Label>
                                        <Input
                                            id="reconciliation_date"
                                            type="date"
                                            value={data.reconciliation_date}
                                            onChange={(e) => setData('reconciliation_date', e.target.value)}
                                            required
                                        />
                                        {errors.reconciliation_date && (
                                            <p className="text-sm text-red-600">{errors.reconciliation_date}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="notes">Observações (opcional)</Label>
                                        <Textarea
                                            id="notes"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            placeholder="Observações sobre esta conciliação..."
                                            rows={3}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Resumo</Label>
                                        <div className="text-sm space-y-1">
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Total de Itens:</span>
                                                <span>{data.items.length}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Pendentes:</span>
                                                <span>{data.items.filter(i => i.status === 'pending').length}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Conciliados:</span>
                                                <span>{data.items.filter(i => i.status === 'reconciled').length}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Divergências:</span>
                                                <span>{data.items.filter(i => i.status === 'discrepancy').length}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {errors.items && (
                                        <p className="text-sm text-red-600">{errors.items}</p>
                                    )}

                                    <Button type="submit" className="w-full" disabled={processing}>
                                        {processing ? 'Processando...' : 'Finalizar Conciliação'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Informações da Conta */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Informações da Conta</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Banco:</span>
                                    <span>{bank_account.bank_name}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Agência:</span>
                                    <span>{bank_account.agency}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Conta:</span>
                                    <span>{bank_account.account_number}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Saldo Inicial:</span>
                                    <span>{formatBRL(bank_account.initial_balance)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}