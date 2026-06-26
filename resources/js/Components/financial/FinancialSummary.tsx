import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { MoneyDisplay } from './MoneyDisplay';
import { ArrowUpRight, ArrowDownRight, TrendingUp } from 'lucide-react';

interface FinancialSummaryProps {
  totalIncome: string | number;
  totalExpense: string | number;
  netBalance: string | number;
  incomeCount?: number;
  expenseCount?: number;
  className?: string;
}

export function FinancialSummary({
  totalIncome,
  totalExpense,
  netBalance,
  incomeCount = 0,
  expenseCount = 0,
  className = ''
}: FinancialSummaryProps) {
  const numericNetBalance = typeof netBalance === 'string' ? parseFloat(netBalance) : netBalance;
  const isPositiveBalance = numericNetBalance >= 0;

  return (
    <div className={`grid gap-4 md:grid-cols-2 lg:grid-cols-4 ${className}`}>
      {/* Total Receitas */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle className="text-sm font-medium">Total Receitas</CardTitle>
          <ArrowUpRight className="h-4 w-4 text-green-500" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold text-green-600">
            <MoneyDisplay amount={totalIncome} direction="in" showSign={false} />
          </div>
          <p className="text-xs text-muted-foreground">
            {incomeCount} lançamento{incomeCount !== 1 ? 's' : ''}
          </p>
        </CardContent>
      </Card>

      {/* Total Despesas */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle className="text-sm font-medium">Total Despesas</CardTitle>
          <ArrowDownRight className="h-4 w-4 text-red-500" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold text-red-600">
            <MoneyDisplay amount={totalExpense} direction="out" showSign={false} />
          </div>
          <p className="text-xs text-muted-foreground">
            {expenseCount} lançamento{expenseCount !== 1 ? 's' : ''}
          </p>
        </CardContent>
      </Card>

      {/* Saldo Líquido */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle className="text-sm font-medium">Saldo Líquido</CardTitle>
          <TrendingUp className={`h-4 w-4 ${isPositiveBalance ? 'text-green-500' : 'text-red-500'}`} />
        </CardHeader>
        <CardContent>
          <div className={`text-2xl font-bold ${isPositiveBalance ? 'text-green-600' : 'text-red-600'}`}>
            <MoneyDisplay amount={netBalance} showSign={true} />
          </div>
          <p className="text-xs text-muted-foreground">
            Receitas - Despesas
          </p>
        </CardContent>
      </Card>

      {/* Pendentes */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle className="text-sm font-medium">Pendentes</CardTitle>
          <div className="h-4 w-4 text-yellow-500" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold text-yellow-600">
            {incomeCount + expenseCount}
          </div>
          <p className="text-xs text-muted-foreground">
            Aguardando pagamento
          </p>
        </CardContent>
      </Card>
    </div>
  );
}