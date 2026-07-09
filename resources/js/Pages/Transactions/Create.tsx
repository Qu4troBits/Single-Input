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
import { ArrowLeft, Save } from 'lucide-react';

interface BankAccount {
  id: string;
  name: string;
}

interface Category {
  id: string;
  name: string;
}

interface TransactionsCreateProps extends PageProps {
  bankAccounts: BankAccount[];
  categories: Category[];
}

export default function Create({ bankAccounts, categories }: TransactionsCreateProps) {
  const { auth } = usePage<PageProps>().props;

  const { data, setData, post, processing, errors } = useForm({
    bank_account_id: '',
    category_id: '',
    description: '',
    amount: '',
    direction: 'out' as 'in' | 'out',
    competence_month: new Date().toISOString().slice(0, 7), // YYYY-MM
    payment_date: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('transactions.store'));
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
      <Head title="Novo Lançamento" />
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Novo Lançamento</h2>
          <p className="text-muted-foreground">
            Registre uma nova transação financeira
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
        <Card>
          <CardHeader>
            <CardTitle>Informações do Lançamento</CardTitle>
            <CardDescription>
              Preencha os dados da transação financeira
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

              {/* Data de pagamento (opcional) */}
              <div className="space-y-2">
                <Label htmlFor="payment_date">Data de Pagamento (opcional)</Label>
                <Input
                  id="payment_date"
                  value={data.payment_date}
                  onChange={(e) => setData('payment_date', e.target.value)}
                  type="date"
                />
                <p className="text-sm text-muted-foreground">
                  Se preenchida, a transação será marcada como paga automaticamente
                </p>
                {errors.payment_date && (
                  <p className="text-sm text-red-500">{errors.payment_date}</p>
                )}
              </div>

              {/* Ações */}
              <div className="flex items-center justify-end gap-4 pt-6 border-t">
                <Link href={route('transactions.index')}>
                  <Button type="button" variant="outline">
                    Cancelar
                  </Button>
                </Link>
                <Button type="submit" disabled={processing}>
                  <Save className="mr-2 h-4 w-4" />
                  {processing ? 'Salvando...' : 'Salvar Lançamento'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        {/* Informações importantes */}
        <Card className="mt-6">
          <CardHeader>
            <CardTitle className="text-sm">Informações Importantes</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground space-y-2">
            <p>• <strong>Competência:</strong> Mês ao qual a transação pertence, independente da data de pagamento.</p>
            <p>• <strong>Data de Pagamento:</strong> Data efetiva do pagamento/recebimento.</p>
            <p>• <strong>Status:</strong> Transações sem data de pagamento ficam como "Pendentes".</p>
            <p>• <strong>Valores:</strong> Use ponto como separador decimal (ex: 1500.50 para R$ 1.500,50).</p>
          </CardContent>
        </Card>
      </div>
    </AuthenticatedLayout>
  );
}