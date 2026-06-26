import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Link } from '@inertiajs/react';
import { formatBRL } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { BankAccount, ReconciliationItem, ReconciliationSummary } from '@/types';

interface BankReconciliationShowProps extends PageProps {
    bank_account: BankAccount;
    pending_items: ReconciliationItem[];
    summary: ReconciliationSummary;
}

export default function Show({ auth, bank_account, pending_items, summary }: BankReconciliationShowProps) {
    const getItemStatusColor = (status: string) => {
        switch (status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'reconciled': return 'bg-green-100 text-green-800';
            case 'discrepancy': return 'bg-red-100 text-red-800';
            case 'adjusted': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getItemStatusText = (status: string) => {
        switch (status) {
            case 'pending': return 'Pendente';
            case 'reconciled': return 'Conciliado';
            case 'discrepancy': return 'Divergência';
            case 'adjusted': return 'Ajustado';
            default: return 'Desconhecido';
        }
    };

    const hasDiscrepancy = summary.discrepancy_items > 0;
    const balanceDifference = Math.abs(
        parseFloat(summary.expected_balance) - parseFloat(summary.actual_balance)
    );

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">
                            Conciliação: {bank_account.name}
                        </h2>
                        <p className="text-muted-foreground">
                            Banco: {bank_account.bank_name} • Agência: {bank_account.agency} • Conta: {bank_account.account_number}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('bank-reconciliation.index')}>
                                Voltar
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={route('bank-reconciliation.import.form', bank_account.id)}>
                                Importar Extrato
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={route('bank-reconciliation.reconcile.form', bank_account.id)}>
                                Conciliação Manual
                            </Link>
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title={`Conciliação: ${bank_account.name}`} />

            <div className="space-y-6">
                {/* Resumo */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Itens Pendentes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {summary.pending_items}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Aguardando conciliação
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Itens Conciliados
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {summary.reconciled_items}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Processados com sucesso
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Saldo Esperado
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatBRL(summary.expected_balance)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Segundo sistema
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Saldo Real
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatBRL(summary.actual_balance)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Segundo extrato
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Alerta de divergência */}
                {hasDiscrepancy && (
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader>
                            <CardTitle className="text-red-800 flex items-center gap-2">
                                <span>⚠️</span>
                                Divergência Detectada
                            </CardTitle>
                            <CardDescription className="text-red-700">
                                Existem {summary.discrepancy_items} item(s) com divergência entre o sistema e o extrato bancário.
                                Diferença de saldo: {formatBRL(balanceDifference.toString())}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button variant="destructive" asChild>
                                <Link href={route('bank-reconciliation.reconcile.form', bank_account.id)}>
                                    Resolver Divergências
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* Itens Pendentes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Itens Pendentes de Conciliação</CardTitle>
                        <CardDescription>
                            Transações que ainda não foram conciliadas com o extrato bancário
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {pending_items.length === 0 ? (
                            <div className="text-center py-8">
                                <p className="text-muted-foreground">
                                    Nenhum item pendente de conciliação. Tudo em dia! ✅
                                </p>
                            </div>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Data</TableHead>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead>Valor</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Transação</TableHead>
                                        <TableHead>Referência</TableHead>
                                        <TableHead>Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {pending_items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>
                                                {formatDate(item.date)}
                                            </TableCell>
                                            <TableCell className="max-w-xs truncate">
                                                {item.description}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatBRL(item.amount)}
                                            </TableCell>
                                            <TableCell>
                                                <Badge className={getItemStatusColor(item.status)}>
                                                    {getItemStatusText(item.status)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {item.transaction_id ? (
                                                    <Link
                                                        href={route('transactions.edit', item.transaction_id)}
                                                        className="text-blue-600 hover:underline text-sm"
                                                    >
                                                        Ver Transação
                                                    </Link>
                                                ) : (
                                                    <span className="text-muted-foreground text-sm">
                                                        Não associada
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {item.bank_statement_id ? (
                                                    <span className="text-sm font-mono">
                                                        {item.bank_statement_id}
                                                    </span>
                                                ) : (
                                                    <span className="text-muted-foreground text-sm">
                                                        Sem referência
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Button size="sm" variant="outline" asChild>
                                                    <Link href={route('bank-reconciliation.reconcile.form', bank_account.id)}>
                                                        Conciliação
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>

                {/* Informações do Resumo */}
                <Card>
                    <CardHeader>
                        <CardTitle>Detalhes do Resumo</CardTitle>
                        <CardDescription>
                            Informações geradas em: {formatDate(summary.generated_at)}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 className="font-semibold mb-2">Totais do Período</h3>
                                <ul className="space-y-1 text-sm">
                                    <li className="flex justify-between">
                                        <span className="text-muted-foreground">Créditos:</span>
                                        <span className="font-medium">{formatBRL(summary.total_credits)}</span>
                                    </li>
                                    <li className="flex justify-between">
                                        <span className="text-muted-foreground">Débitos:</span>
                                        <span className="font-medium">{formatBRL(summary.total_debits)}</span>
                                    </li>
                                    <li className="flex justify-between pt-2 border-t">
                                        <span className="text-muted-foreground">Saldo Líquido:</span>
                                        <span className="font-medium">
                                            {formatBRL(
                                                (parseFloat(summary.total_credits) - parseFloat(summary.total_debits)).toString()
                                            )}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h3 className="font-semibold mb-2">Status da Conciliação</h3>
                                <ul className="space-y-1 text-sm">
                                    <li className="flex justify-between">
                                        <span className="text-muted-foreground">Pendentes:</span>
                                        <Badge variant="outline">{summary.pending_items}</Badge>
                                    </li>
                                    <li className="flex justify-between">
                                        <span className="text-muted-foreground">Conciliados:</span>
                                        <Badge variant="outline" className="bg-green-50">
                                            {summary.reconciled_items}
                                        </Badge>
                                    </li>
                                    <li className="flex justify-between">
                                        <span className="text-muted-foreground">Divergências:</span>
                                        <Badge variant="outline" className={hasDiscrepancy ? 'bg-red-50' : ''}>
                                            {summary.discrepancy_items}
                                        </Badge>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}