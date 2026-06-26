import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { formatBRL } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { ArrowUpRight, ArrowDownRight, CheckCircle, Clock, XCircle, AlertCircle } from 'lucide-react';

interface Transaction {
  id: string;
  bank_account_id: string;
  category_id: string;
  description: string;
  amount: string;
  direction: 'in' | 'out';
  status: 'pending' | 'paid' | 'cancelled' | 'reversed';
  competence_month: string;
  payment_date: string | null;
  created_at: string;
  updated_at: string;
}

interface BankAccount {
  id: string;
  name: string;
}

interface Category {
  id: string;
  name: string;
}

interface TransactionsIndexProps extends PageProps {
  transactions: Transaction[];
  bankAccounts: Record<string, BankAccount>;
  categories: Record<string, Category>;
}

export default function Index({ transactions, bankAccounts, categories }: TransactionsIndexProps) {
  const { auth } = usePage<PageProps>().props;

  const getStatusIcon = (status: Transaction['status']) => {
    switch (status) {
      case 'paid':
        return <CheckCircle className="h-4 w-4 text-green-500" />;
      case 'pending':
        return <Clock className="h-4 w-4 text-yellow-500" />;
      case 'cancelled':
        return <XCircle className="h-4 w-4 text-red-500" />;
      case 'reversed':
        return <AlertCircle className="h-4 w-4 text-orange-500" />;
    }
  };

  const getStatusBadge = (status: Transaction['status']) => {
    switch (status) {
      case 'paid':
        return <Badge variant="success">Pago</Badge>;
      case 'pending':
        return <Badge variant="warning">Pendente</Badge>;
      case 'cancelled':
        return <Badge variant="destructive">Cancelado</Badge>;
      case 'reversed':
        return <Badge variant="secondary">Estornado</Badge>;
    }
  };

  const getDirectionIcon = (direction: Transaction['direction']) => {
    return direction === 'in' ? (
      <ArrowUpRight className="h-4 w-4 text-green-500" />
    ) : (
      <ArrowDownRight className="h-4 w-4 text-red-500" />
    );
  };

  const getDirectionBadge = (direction: Transaction['direction']) => {
    return direction === 'in' ? (
      <Badge variant="success">Receita</Badge>
    ) : (
      <Badge variant="destructive">Despesa</Badge>
    );
  };

  const getBankAccountName = (bankAccountId: string) => {
    return bankAccounts[bankAccountId]?.name || 'Desconhecida';
  };

  const getCategoryName = (categoryId: string) => {
    return categories[categoryId]?.name || 'Desconhecida';
  };

  const getSignedAmount = (transaction: Transaction) => {
    const amount = parseFloat(transaction.amount);
    return transaction.direction === 'in' ? amount : -amount;
  };

  const calculateTotal = () => {
    return transactions.reduce((total, transaction) => {
      return total + getSignedAmount(transaction);
    }, 0);
  };

  const calculateTotalByDirection = (direction: 'in' | 'out') => {
    return transactions
      .filter(t => t.direction === direction)
      .reduce((total, transaction) => {
        return total + parseFloat(transaction.amount);
      }, 0);
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-3xl font-bold tracking-tight">Lançamentos</h2>
            <p className="text-muted-foreground">
              Gerencie todas as transações financeiras da sua empresa
            </p>
          </div>
          <Link href={route('transactions.create')}>
            <Button>
              <ArrowUpRight className="mr-2 h-4 w-4" />
              Novo Lançamento
            </Button>
          </Link>
        </div>
      }
    >
      <Head title="Lançamentos" />

      <div className="space-y-6">
        {/* Cards de resumo */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Receitas</CardTitle>
              <ArrowUpRight className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatBRL(calculateTotalByDirection('in').toString())}
              </div>
              <p className="text-xs text-muted-foreground">
                {transactions.filter(t => t.direction === 'in').length} lançamentos
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Despesas</CardTitle>
              <ArrowDownRight className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">
                {formatBRL(calculateTotalByDirection('out').toString())}
              </div>
              <p className="text-xs text-muted-foreground">
                {transactions.filter(t => t.direction === 'out').length} lançamentos
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Saldo Líquido</CardTitle>
              <div className="h-4 w-4" />
            </CardHeader>
            <CardContent>
              <div className={`text-2xl font-bold ${calculateTotal() >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                {formatBRL(calculateTotal().toString())}
              </div>
              <p className="text-xs text-muted-foreground">
                {transactions.length} lançamentos no total
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pendentes</CardTitle>
              <Clock className="h-4 w-4 text-yellow-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">
                {transactions.filter(t => t.status === 'pending').length}
              </div>
              <p className="text-xs text-muted-foreground">
                Aguardando pagamento
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Tabela de transações */}
        <Card>
          <CardHeader>
            <CardTitle>Lista de Lançamentos</CardTitle>
            <CardDescription>
              Todas as transações financeiras registradas no sistema
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Data</TableHead>
                  <TableHead>Descrição</TableHead>
                  <TableHead>Categoria</TableHead>
                  <TableHead>Conta Bancária</TableHead>
                  <TableHead>Valor</TableHead>
                  <TableHead>Tipo</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Competência</TableHead>
                  <TableHead>Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {transactions.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={9} className="text-center py-8 text-muted-foreground">
                      Nenhum lançamento encontrado. Crie seu primeiro lançamento!
                    </TableCell>
                  </TableRow>
                ) : (
                  transactions.map((transaction) => (
                    <TableRow key={transaction.id}>
                      <TableCell className="whitespace-nowrap">
                        {formatDate(transaction.created_at)}
                      </TableCell>
                      <TableCell className="font-medium">
                        {transaction.description}
                      </TableCell>
                      <TableCell>
                        {getCategoryName(transaction.category_id)}
                      </TableCell>
                      <TableCell>
                        {getBankAccountName(transaction.bank_account_id)}
                      </TableCell>
                      <TableCell className={`font-semibold ${transaction.direction === 'in' ? 'text-green-600' : 'text-red-600'}`}>
                        {transaction.direction === 'in' ? '+' : '-'}
                        {formatBRL(transaction.amount)}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          {getDirectionIcon(transaction.direction)}
                          {getDirectionBadge(transaction.direction)}
                        </div>
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          {getStatusIcon(transaction.status)}
                          {getStatusBadge(transaction.status)}
                        </div>
                      </TableCell>
                      <TableCell>
                        {transaction.competence_month}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Link href={route('transactions.edit', transaction.id)}>
                            <Button variant="outline" size="sm">
                              Editar
                            </Button>
                          </Link>
                          {transaction.status === 'pending' && (
                            <Link
                              href={route('transactions.markAsPaid', transaction.id)}
                              method="post"
                              as="button"
                            >
                              <Button variant="default" size="sm">
                                Marcar como Pago
                              </Button>
                            </Link>
                          )}
                          {transaction.status === 'pending' && (
                            <Link
                              href={route('transactions.markAsCancelled', transaction.id)}
                              method="post"
                              as="button"
                            >
                              <Button variant="destructive" size="sm">
                                Cancelar
                              </Button>
                            </Link>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </AuthenticatedLayout>
  );
}