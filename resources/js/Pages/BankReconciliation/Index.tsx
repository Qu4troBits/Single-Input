import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Select } from '@/Components/ui/select';
import { Link } from '@inertiajs/react';
import { formatBRL } from '@/Utils/formatCurrency';
import { BankAccount, ReconciliationSummary } from '@/types';

interface BankReconciliationIndexProps extends PageProps {
    bank_accounts: BankAccount[];
    reconciliation_summaries: Array<{
        bank_account_id: string;
        bank_account_name: string;
        summary: ReconciliationSummary;
    }>;
}

export default function Index({ auth, bank_accounts, reconciliation_summaries }: BankReconciliationIndexProps) {
    const getStatusColor = (pendingItems: number) => {
        if (pendingItems === 0) return 'bg-green-100 text-green-800';
        if (pendingItems <= 5) return 'bg-yellow-100 text-yellow-800';
        return 'bg-red-100 text-red-800';
    };

    const getStatusText = (pendingItems: number) => {
        if (pendingItems === 0) return 'Conciliação em dia';
        if (pendingItems <= 5) return 'Pendências baixas';
        return 'Pendências críticas';
    };

    return (
        <AuthenticatedLayout
        >
            <Head title="Conciliação Bancária" />
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">Conciliação Bancária</h2>
                    <p className="text-muted-foreground">
                        Acompanhe e concilie suas transações bancárias
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button asChild>
                        <Link href={route('bank-accounts.create')}>
                            Nova Conta Bancária
                        </Link>
                    </Button>
                </div>
            </div>
            <div className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Resumo das Contas Bancárias</CardTitle>
                        <CardDescription>
                            Status de conciliação das suas contas bancárias
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Conta Bancária</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Itens Pendentes</TableHead>
                                    <TableHead>Itens Conciliados</TableHead>
                                    <TableHead>Saldo Esperado</TableHead>
                                    <TableHead>Saldo Real</TableHead>
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {reconciliation_summaries.map((item) => (
                                    <TableRow key={item.bank_account_id}>
                                        <TableCell className="font-medium">
                                            {item.bank_account_name}
                                        </TableCell>
                                        <TableCell>
                                            <Badge className={getStatusColor(item.summary.pending_items)}>
                                                {getStatusText(item.summary.pending_items)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <span className="font-semibold">{item.summary.pending_items}</span>
                                        </TableCell>
                                        <TableCell>
                                            <span className="font-semibold">{item.summary.reconciled_items}</span>
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {formatBRL(item.summary.expected_balance)}
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {formatBRL(item.summary.actual_balance)}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Button size="sm" variant="outline" asChild>
                                                    <Link href={route('bank-reconciliation.show', { bank_account: item.bank_account_id })}>
                                                        Detalhes
                                                    </Link>
                                                </Button>
                                                <Button size="sm" asChild>
                                                    <Link href={route('bank-reconciliation.import.form', { bank_account: item.bank_account_id })}>
                                                        Importar Extrato
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Importar Extrato Bancário</CardTitle>
                            <CardDescription>
                                Importe extratos em formato CSV, OFX ou PDF
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground mb-4">
                                Suportamos os seguintes formatos:
                            </p>
                            <ul className="space-y-2 text-sm">
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    CSV (Comma Separated Values)
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    OFX (Open Financial Exchange)
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    PDF com extração de texto
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Entrada manual
                                </li>
                            </ul>
                            <Button className="mt-4" asChild>
                                <Link href={route('bank-accounts.index')}>
                                    Selecionar Conta para Importar
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Conciliação Manual</CardTitle>
                            <CardDescription>
                                Concilie transações pendentes manualmente
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground mb-4">
                                Processo de conciliação:
                            </p>
                            <ol className="space-y-2 text-sm">
                                <li className="flex items-center">
                                    <span className="mr-2">1.</span>
                                    Selecione uma conta bancária
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">2.</span>
                                    Visualize transações pendentes
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">3.</span>
                                    Associe com itens do extrato
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">4.</span>
                                    Marque como conciliado
                                </li>
                            </ol>
                            <Button className="mt-4" variant="outline" asChild>
                                <Link href={route('transactions.index')}>
                                    Ver Transações Pendentes
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}