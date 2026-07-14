import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, Download, Printer, Share2 } from 'lucide-react';
import { MoneyDisplay } from '@/Components/financial/MoneyDisplay';
import { formatBRL } from '@/Utils/formatCurrency';

interface FinancialReportItem {
  code: string;
  description: string;
  amount: string;
  category_id?: string;
  category_name?: string;
  notes?: string;
}

interface FinancialReportSummary {
  period: string;
  title: string;
  total_revenue: string;
  total_expenses: string;
  gross_profit: string;
  operating_expenses: string;
  operating_profit: string;
  net_profit: string;
  item_count: number;
}

interface MonthlyDreProps extends PageProps {
  report: {
    period: string;
    title: string;
    items: FinancialReportItem[];
    summary: FinancialReportSummary;
  };
  period: string;
}

export default function MonthlyDre({ report, period }: MonthlyDreProps) {
  const { auth } = usePage<PageProps>().props;

  const formatPeriod = (periodStr: string) => {
    const [year, month] = periodStr.split('-');
    const date = new Date(parseInt(year), parseInt(month) - 1, 1);
    return date.toLocaleDateString('pt-BR', {
      month: 'long',
      year: 'numeric',
    });
  };

  const getItemColor = (item: FinancialReportItem) => {
    if (item.code.startsWith('R')) return 'text-green-600';
    if (item.code.startsWith('E') || item.code === 'COGS') return 'text-red-600';
    if (['GP', 'OP', 'NP'].includes(item.code)) return 'text-blue-600';
    return 'text-gray-600';
  };

  const getItemIndent = (item: FinancialReportItem) => {
    if (item.code.startsWith('R') || item.code === 'COGS') return 'ml-0';
    if (item.code.startsWith('E')) return 'ml-4';
    if (item.code === 'GP') return 'ml-8';
    if (item.code === 'OP') return 'ml-12';
    if (item.code === 'NP') return 'ml-16';
    return 'ml-0';
  };

  const handlePrint = () => {
    window.print();
  };

  const handleDownload = () => {
    // Em uma implementação real, isso geraria um PDF ou Excel
    alert('Funcionalidade de download em desenvolvimento');
  };

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: `DRE ${formatPeriod(period)}`,
        text: `Demonstrativo de Resultados do Exercício - ${formatPeriod(period)}`,
        url: window.location.href,
      });
    } else {
      alert('Compartilhamento não suportado neste navegador');
    }
  };

  return (
    <AuthenticatedLayout
    >
      <Head title={`DRE Mensal - ${formatPeriod(period)}`} />
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">DRE Mensal</h2>
          <p className="text-muted-foreground">
            {formatPeriod(period)}
          </p>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={handleShare}>
            <Share2 className="mr-2 h-4 w-4" />
            Compartilhar
          </Button>
          <Button variant="outline" size="sm" onClick={handleDownload}>
            <Download className="mr-2 h-4 w-4" />
            Download
          </Button>
          <Button variant="outline" size="sm" onClick={handlePrint}>
            <Printer className="mr-2 h-4 w-4" />
            Imprimir
          </Button>
          <Link href={route('reports.index')}>
            <Button variant="outline" size="sm">
              <ArrowLeft className="mr-2 h-4 w-4" />
              Voltar
            </Button>
          </Link>
        </div>
      </div>
      <div className="space-y-6">
        {/* Resumo do relatório */}
        <Card>
          <CardHeader>
            <CardTitle>Resumo do Relatório</CardTitle>
            <CardDescription>
              {report.title} - {report.summary.period}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
              <div className="space-y-1">
                <p className="text-sm font-medium text-muted-foreground">Total Receitas</p>
                <p className="text-2xl font-bold text-green-600">
                  <MoneyDisplay amount={report.summary.total_revenue} showSign={false} />
                </p>
              </div>
              <div className="space-y-1">
                <p className="text-sm font-medium text-muted-foreground">Total Despesas</p>
                <p className="text-2xl font-bold text-red-600">
                  <MoneyDisplay amount={report.summary.total_expenses} showSign={false} />
                </p>
              </div>
              <div className="space-y-1">
                <p className="text-sm font-medium text-muted-foreground">Lucro Líquido</p>
                <p className={`text-2xl font-bold ${parseFloat(report.summary.net_profit) >= 0 ? 'text-blue-600' : 'text-red-600'}`}>
                  <MoneyDisplay amount={report.summary.net_profit} showSign={true} />
                </p>
              </div>
              <div className="space-y-1">
                <p className="text-sm font-medium text-muted-foreground">Margem de Lucro</p>
                <p className={`text-2xl font-bold ${parseFloat(report.summary.net_profit) >= 0 ? 'text-blue-600' : 'text-red-600'}`}>
                  {report.summary.total_revenue === '0.00' ? '0.00%' :
                    `${((parseFloat(report.summary.net_profit) / parseFloat(report.summary.total_revenue)) * 100).toFixed(2)}%`}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Detalhes do DRE */}
        <Card>
          <CardHeader>
            <CardTitle>Demonstrativo de Resultados</CardTitle>
            <CardDescription>
              Detalhamento das receitas, despesas e lucros
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {/* Cabeçalho da tabela */}
              <div className="grid grid-cols-12 gap-4 font-semibold text-sm text-muted-foreground border-b pb-2">
                <div className="col-span-2">Código</div>
                <div className="col-span-6">Descrição</div>
                <div className="col-span-4 text-right">Valor (R$)</div>
              </div>

              {/* Itens do relatório */}
              {report.items.map((item) => (
                <div
                  key={`${item.code}-${item.description}`}
                  className={`grid grid-cols-12 gap-4 py-2 ${getItemIndent(item)}`}
                >
                  <div className="col-span-2 font-mono font-medium">
                    {item.code}
                  </div>
                  <div className="col-span-6">
                    <div className="font-medium">{item.description}</div>
                    {item.category_name && (
                      <div className="text-xs text-muted-foreground">
                        Categoria: {item.category_name}
                      </div>
                    )}
                    {item.notes && (
                      <div className="text-xs text-muted-foreground">
                        {item.notes}
                      </div>
                    )}
                  </div>
                  <div className={`col-span-4 text-right font-semibold ${getItemColor(item)}`}>
                    {item.code.startsWith('R') ? '+' : '-'}
                    {formatBRL(item.amount)}
                  </div>
                </div>
              ))}

              {/* Linhas de separação para itens de lucro */}
              <div className="border-t border-gray-200 my-4"></div>

              {/* Totais */}
              <div className="space-y-2">
                <div className="grid grid-cols-12 gap-4 py-2">
                  <div className="col-span-8 font-semibold text-right">Total Receitas:</div>
                  <div className="col-span-4 text-right font-bold text-green-600">
                    +{formatBRL(report.summary.total_revenue)}
                  </div>
                </div>

                <div className="grid grid-cols-12 gap-4 py-2">
                  <div className="col-span-8 font-semibold text-right">Total Despesas:</div>
                  <div className="col-span-4 text-right font-bold text-red-600">
                    -{formatBRL(report.summary.total_expenses)}
                  </div>
                </div>

                <div className="grid grid-cols-12 gap-4 py-2 border-t border-gray-300 pt-4">
                  <div className="col-span-8 font-bold text-right">Lucro Líquido:</div>
                  <div className={`col-span-4 text-right font-bold ${parseFloat(report.summary.net_profit) >= 0 ? 'text-blue-600' : 'text-red-600'}`}>
                    {parseFloat(report.summary.net_profit) >= 0 ? '+' : ''}
                    {formatBRL(report.summary.net_profit)}
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Análises adicionais */}
        <div className="grid gap-4 md:grid-cols-2">
          {/* Receitas por categoria */}
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Receitas por Categoria</CardTitle>
            </CardHeader>
            <CardContent>
              {report.items
                .filter(item => item.code.startsWith('R'))
                .map((item) => (
                  <div key={item.code} className="flex justify-between items-center py-1">
                    <span className="text-sm">{item.category_name || item.description}</span>
                    <span className="font-medium text-green-600">
                      {formatBRL(item.amount)}
                    </span>
                  </div>
                ))}
            </CardContent>
          </Card>

          {/* Despesas por categoria */}
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Despesas por Categoria</CardTitle>
            </CardHeader>
            <CardContent>
              {report.items
                .filter(item => item.code.startsWith('E') || item.code === 'COGS')
                .map((item) => (
                  <div key={item.code} className="flex justify-between items-center py-1">
                    <span className="text-sm">{item.category_name || item.description}</span>
                    <span className="font-medium text-red-600">
                      {formatBRL(item.amount)}
                    </span>
                  </div>
                ))}
            </CardContent>
          </Card>
        </div>

        {/* Informações técnicas */}
        <Card className="bg-gray-50">
          <CardHeader>
            <CardTitle className="text-sm">Informações Técnicas</CardTitle>
          </CardHeader>
          <CardContent className="text-xs text-muted-foreground space-y-1">
            <p><strong>Período:</strong> {report.summary.period}</p>
            <p><strong>Data de geração:</strong> {new Date().toLocaleDateString('pt-BR')}</p>
            <p><strong>Total de itens:</strong> {report.summary.item_count}</p>
            <p><strong>Usuário:</strong> {auth.user.name}</p>
            <p><strong>Empresa:</strong> {auth.user.current_tenant?.name || 'N/A'}</p>
          </CardContent>
        </Card>
      </div>

      {/* Estilos para impressão */}
      <style>
        {`
          @media print {
            .no-print {
              display: none !important;
            }
            
            body {
              font-size: 12pt;
            }
            
            .print-break {
              page-break-before: always;
            }
          }
        `}
      </style>
    </AuthenticatedLayout>
  );
}