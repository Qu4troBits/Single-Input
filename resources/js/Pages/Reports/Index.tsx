import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { BarChart3, PieChart, TrendingUp, Calendar, FileText } from 'lucide-react';

interface ReportsIndexProps extends PageProps {
  availablePeriods: string[];
}

export default function Index({ availablePeriods }: ReportsIndexProps) {
  const { auth } = usePage<PageProps>().props;

  const currentYearMonth = new Date().toISOString().slice(0, 7);
  const currentYear = new Date().getFullYear().toString();
  const currentQuarter = Math.floor((new Date().getMonth() + 3) / 3);

  const reportCards = [
    {
      title: 'DRE Mensal',
      description: 'Demonstrativo de Resultados do Exercício por mês',
      icon: <Calendar className="h-6 w-6" />,
      href: route('reports.monthly.dre', { yearMonth: currentYearMonth }),
      color: 'bg-blue-50 border-blue-200 text-blue-700',
    },
    {
      title: 'DRE Trimestral',
      description: 'Demonstrativo de Resultados do Exercício por trimestre',
      icon: <BarChart3 className="h-6 w-6" />,
      href: route('reports.quarterly.dre', { year: currentYear, quarter: currentQuarter }),
      color: 'bg-green-50 border-green-200 text-green-700',
    },
    {
      title: 'DRE Anual',
      description: 'Demonstrativo de Resultados do Exercício por ano',
      icon: <FileText className="h-6 w-6" />,
      href: route('reports.yearly.dre', { year: currentYear }),
      color: 'bg-purple-50 border-purple-200 text-purple-700',
    },
    {
      title: 'Margem de Lucro',
      description: 'Tendência da margem de lucro ao longo do tempo',
      icon: <TrendingUp className="h-6 w-6" />,
      href: route('reports.profit.margin.trend'),
      color: 'bg-orange-50 border-orange-200 text-orange-700',
    },
    {
      title: 'Receitas por Categoria',
      description: 'Distribuição das receitas por categoria',
      icon: <PieChart className="h-6 w-6" />,
      href: route('reports.revenue.by.category'),
      color: 'bg-teal-50 border-teal-200 text-teal-700',
    },
    {
      title: 'Despesas por Categoria',
      description: 'Distribuição das despesas por categoria',
      icon: <PieChart className="h-6 w-6" />,
      href: route('reports.expenses.by.category'),
      color: 'bg-red-50 border-red-200 text-red-700',
    },
  ];

  return (
    <AuthenticatedLayout
    >
      <Head title="Relatórios Financeiros" />
      <div>
        <h2 className="text-3xl font-bold tracking-tight">Relatórios Financeiros</h2>
        <p className="text-muted-foreground">
          Análises e demonstrativos financeiros da sua empresa
        </p>
      </div>
      <div className="space-y-6">
        {/* Períodos disponíveis */}
        <Card>
          <CardHeader>
            <CardTitle>Períodos Disponíveis</CardTitle>
            <CardDescription>
              Selecione um período para visualizar relatórios específicos
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex flex-wrap gap-2">
              {availablePeriods.length === 0 ? (
                <p className="text-muted-foreground">Nenhum período disponível. Crie transações primeiro.</p>
              ) : (
                availablePeriods.map((period) => (
                  <Link
                    key={period}
                    href={route('reports.monthly.dre', { period: period })}
                  >
                    <Button variant="outline" size="sm">
                      {new Date(period + '-01').toLocaleDateString('pt-BR', {
                        month: 'long',
                        year: 'numeric',
                      })}
                    </Button>
                  </Link>
                ))
              )}
            </div>
          </CardContent>
        </Card>

        {/* Cards de relatórios */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {reportCards.map((card) => (
            <Link key={card.title} href={card.href}>
              <Card className={`hover:shadow-md transition-shadow cursor-pointer ${card.color}`}>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-lg font-semibold">{card.title}</CardTitle>
                  <div className={card.color.replace('bg-', 'text-').split(' ')[0]}>
                    {card.icon}
                  </div>
                </CardHeader>
                <CardContent>
                  <p className="text-sm opacity-80">{card.description}</p>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>

        {/* Informações importantes */}
        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Informações sobre os Relatórios</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground space-y-2">
            <p>• <strong>DRE (Demonstrativo de Resultados do Exercício):</strong> Mostra receitas, despesas e lucro em um período.</p>
            <p>• <strong>Receitas:</strong> Entradas de recursos (vendas, serviços prestados, etc.).</p>
            <p>• <strong>Despesas:</strong> Saídas de recursos (salários, aluguel, fornecedores, etc.).</p>
            <p>• <strong>Lucro Bruto:</strong> Receitas - Custo das Mercadorias Vendidas.</p>
            <p>• <strong>Lucro Operacional:</strong> Lucro Bruto - Despesas Operacionais.</p>
            <p>• <strong>Lucro Líquido:</strong> Lucro Operacional - Impostos + Itens Não Operacionais.</p>
            <p>• <strong>Margem de Lucro:</strong> (Lucro Líquido / Receitas) × 100%.</p>
          </CardContent>
        </Card>
      </div>
    </AuthenticatedLayout>
  );
}