import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Save, AlertCircle, CheckCircle, Clock, XCircle } from 'lucide-react';
import { formatBRL } from '@/Utils/formatCurrency';

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

interface TransactionsEditProps extends PageProps {
  transaction: Transaction;
  bankAccounts: BankAccount[];
  categories: Category[];
}

export default function Edit({ transaction, bankAccounts, categories }: TransactionsEditProps) {
  const { auth } = usePage<PageProps>().props;

  const { data, setData, put, processing, errors } = useForm({
    bank_account_id: transaction.bank_account_id,
    category_id: transaction.category_id,
    description: transaction.description,
    amount: transaction.amount,
    direction: transaction.direction,
    competence_month: transaction.competence_month,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('transactions.update', { id: transaction.id }));
  };

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

  const generateCompetenceMonths = () => {
    const months = [];
    const currentDate = new Date();

    // Últimos 12 meses
    for (let i = 0; i < 12; i++) {
      const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const value = `${year}-${month}`;
      const label = new Date(year, parseInt(month) - 1).toLocaleDateString('pt-BR', {
        month: 'long',
        year: 'numeric',
      });

      months.push({ value, label: label.charAt(0).toUpperCase() + label.slice(1) });
    }

    return months;
  };

  const competenceMonths = generateCompetenceMonths();

  return (
    <AuthenticatedLayout
    >
      <Head title="Editar Lançamento" />
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Editar Lançamento</h2>
          <p className="text-muted-foreground">
            Atualize os dados da transação financeira
          </p>
        </div>
        <Link href={route('transactions.index')}>
          <Button variant="outline">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Voltar
          </Button>
        </Link>
      </div>
      <div className="max-w-2xl mx-auto">
        {/* Informações da transação */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="text-sm">Informações da Transação</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label className="text-muted-foreground">ID</Label>
                <p className="font-mono text-sm">{transaction.id}</p>
              </div>
              <div>
                <Label className="text-muted-foreground">Status</Label>
                <div className="flex items-center gap-2">
                  {getStatusIcon(transaction.status)}
                  {getStatusBadge(transaction.status)}
                </div>
              </div>
              <div>
                <Label className="text-muted-foreground">Criado em</Label>
                <p>{new Date(transaction.created_at).toLocaleDateString('pt-BR')}</p>
              </div>
              <div>
                <Label className="text-muted-foreground">Última atualização</Label>
                <p>{new Date(transaction.updated_at).toLocaleDateString('pt-BR')}</p>
              </div>
            </div>

            {transaction.payment_date && (
              <div>
                <Label className="text-muted-foreground">Data de Pagamento</Label>
                <p>{new Date(transaction.payment_date).toLocaleDateString('pt-BR')}</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Formulário de edição */}
        <Card>
          <CardHeader>
            <CardTitle>Editar Informações</CardTitle>
            <CardDescription>
              Atualize os dados da transação financeira
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Tipo de transação */}
              <div className="space-y-4">
                <Label>Tipo de Transação</Label>
                <RadioGroup
                  value={data.direction}
                  onValueChange={(value: 'in' | 'out') => setData('direction', value)}
                  className="flex gap-4"
                >
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem value="in" id="direction-in" />
                    <Label htmlFor="direction-in" className="cursor-pointer">
                      <div className="flex items-center gap-2">
                        <div className="h-3 w-3 rounded-full bg-green-500" />
                        <span>Receita</span>
                      </div>
                    </Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem value="out" id="direction-out" />
                    <Label htmlFor="direction-out" className="cursor-pointer">
                      <div className="flex items-center gap-2">
                        <div className="h-3 w-3 rounded-full bg-red-500" />
                        <span>Despesa</span>
                      </div>
                    </Label>
                  </div>
                </RadioGroup>
                {errors.direction && (
                  <p className="text-sm text-red-500">{errors.direction}</p>
                )}
              </div>

              {/* Conta bancária */}
              <div className="space-y-2">
                <Label htmlFor="bank_account_id">Conta Bancária *</Label>
                <Select
                  value={data.bank_account_id}
                  onValueChange={(value) => setData('bank_account_id', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione uma conta bancária" />
                  </SelectTrigger>
                  <SelectContent>
                    {bankAccounts.map((account) => (
                      <SelectItem key={account.id} value={account.id}>
                        {account.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.bank_account_id && (
                  <p className="text-sm text-red-500">{errors.bank_account_id}</p>
                )}
              </div>

              {/* Categoria */}
              <div className="space-y-2">
                <Label htmlFor="category_id">Categoria *</Label>
                <Select
                  value={data.category_id}
                  onValueChange={(value) => setData('category_id', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione uma categoria" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map((category) => (
                      <SelectItem key={category.id} value={category.id}>
                        {category.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.category_id && (
                  <p className="text-sm text-red-500">{errors.category_id}</p>
                )}
              </div>

              {/* Descrição */}
              <div className="space-y-2">
                <Label htmlFor="description">Descrição *</Label>
                <Textarea
                  id="description"
                  value={data.description}
                  onChange={(e) => setData('description', e.target.value)}
                  placeholder="Ex: Pagamento de fornecedor, Recebimento de cliente, etc."
                  className="min-h-[100px]"
                />
                {errors.description && (
                  <p className="text-sm text-red-500">{errors.description}</p>
                )}
              </div>

              {/* Valor */}
              <div className="space-y-2">
                <Label htmlFor="amount">Valor *</Label>
                <div className="relative">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                    R$
                  </span>
                  <Input
                    id="amount"
                    value={data.amount}
                    onChange={(e) => setData('amount', e.target.value)}
                    placeholder="0,00"
                    className="pl-10"
                    type="number"
                    step="0.01"
                    min="0"
                  />
                </div>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <span>Valor atual:</span>
                  <span className={`font-semibold ${transaction.direction === 'in' ? 'text-green-600' : 'text-red-600'}`}>
                    {transaction.direction === 'in' ? '+' : '-'}
                    {formatBRL(transaction.amount)}
                  </span>
                </div>
                {errors.amount && (
                  <p className="text-sm text-red-500">{errors.amount}</p>
                )}
              </div>

              {/* Competência */}
              <div className="space-y-2">
                <Label htmlFor="competence_month">Competência *</Label>
                <Select
                  value={data.competence_month}
                  onValueChange={(value) => setData('competence_month', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione o mês de competência" />
                  </SelectTrigger>
                  <SelectContent>
                    {competenceMonths.map((month) => (
                      <SelectItem key={month.value} value={month.value}>
                        {month.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.competence_month && (
                  <p className="text-sm text-red-500">{errors.competence_month}</p>
                )}
              </div>

              {/* Ações */}
              <div className="flex items-center justify-between gap-4 pt-6 border-t">
                <div className="flex items-center gap-4">
                  {transaction.status === 'pending' && (
                    <Link
                      href={route('transactions.markAsPaid', { id: transaction.id })}
                      method="post"
                      as="button"
                    >
                      <Button type="button" variant="default">
                        <CheckCircle className="mr-2 h-4 w-4" />
                        Marcar como Pago
                      </Button>
                    </Link>
                  )}
                  {transaction.status === 'pending' && (
                    <Link
                      href={route('transactions.markAsCancelled', { id: transaction.id })}
                      method="post"
                      as="button"
                    >
                      <Button type="button" variant="destructive">
                        <XCircle className="mr-2 h-4 w-4" />
                        Cancelar
                      </Button>
                    </Link>
                  )}
                </div>
                <div className="flex items-center gap-4">
                  <Link href={route('transactions.index')}>
                    <Button type="button" variant="outline">
                      Cancelar
                    </Button>
                  </Link>
                  <Button type="submit" disabled={processing}>
                    <Save className="mr-2 h-4 w-4" />
                    {processing ? 'Salvando...' : 'Salvar Alterações'}
                  </Button>
                </div>
              </div>
            </form>
          </CardContent>
        </Card>

        {/* Avisos importantes */}
        {transaction.status !== 'pending' && (
          <Card className="mt-6 border-yellow-200 bg-yellow-50">
            <CardHeader>
              <CardTitle className="text-sm flex items-center gap-2 text-yellow-800">
                <AlertCircle className="h-4 w-4" />
                Aviso Importante
              </CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-yellow-700 space-y-2">
              <p>
                Esta transação está com status <strong>{transaction.status === 'paid' ? 'PAGA' : 'CANCELADA'}</strong>.
                Algumas alterações podem ser limitadas.
              </p>
              {transaction.status === 'paid' && (
                <p>
                  • Para alterar o status, você precisa primeiro estornar a transação.
                </p>
              )}
              {transaction.status === 'cancelled' && (
                <p>
                  • Transações canceladas não podem ser reativadas. Crie uma nova transação se necessário.
                </p>
              )}
            </CardContent>
          </Card>
        )}
      </div>
    </AuthenticatedLayout>
  );
}