import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { formatCurrency } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { ArrowLeft, Download, Edit, Trash2, BarChart3, AlertCircle } from 'lucide-react';

interface DreLine {
  id: string;
  code: string;
  description: string; 
  amount: string;
  formatted_amount: string;
  type: string;
  type_label: string;
  level: number;
  is_operating: boolean;
  parent_id: string | null;
  category_id: string | null;
  category_name: string | null;
  notes: string | null;
}

interface Dre {
  id: string;
  title: string;
  period: {
    start: string;
    end: string;
    type: string;
    formatted: string;
  };
  category_id: string | null;
  scenario: string;
  lines: DreLine[];
  totals: {
    revenue: {
      amount: string;
      formatted: string;
    };
    expenses: {
      amount: string;
      formatted: string;
    };
    net_profit: {
      amount: string;
      formatted: string;
    };
    gross_profit: {
      amount: string;
      formatted: string;
    };
    operating_profit: {
      amount: string;
      formatted: string;
    };
    ebitda: {
      amount: string;
      formatted: string;
    };
    ebit: {
      amount: string;
      formatted: string;
    };
  };
  generated_at: string | null;
}

interface Props {
  dre: Dre;
}

const exportFormats = [
  { value: 'pdf', label: 'PDF' },
  { value: 'excel', label: 'Excel' },
  { value: 'csv', label: 'CSV' },
  { value: 'json', label: 'JSON' },
];

export default function DreShow({ dre }: Props) {
  const [selectedFormat, setSelectedFormat] = useState('pdf');
  const [isDeleting, setIsDeleting] = useState(false);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

  const getScenarioBadge = (scenario: string) => {
    const colors: Record<string, string> = {
      base: 'bg-blue-100 text-blue-800',
      optimistic: 'bg-green-100 text-green-800',
      pessimistic: 'bg-red-100 text-red-800',
      conservative: 'bg-yellow-100 text-yellow-800',
    };

    const labels: Record<string, string> = {
      base: 'Base',
      optimistic: 'Otimista',
      pessimistic: 'Pessimista',
      conservative: 'Conservador',
    };

    return (
      <Badge className={colors[scenario] || 'bg-gray-100 text-gray-800'}>
        {labels[scenario] || scenario}
      </Badge>
    );
  };

  const getTypeBadge = (type: string) => {
    const colors: Record<string, string> = {
      revenue: 'bg-green-100 text-green-800',
      expense: 'bg-red-100 text-red-800',
      profit: 'bg-blue-100 text-blue-800',
    };

    return (
      <Badge className={colors[type] || 'bg-gray-100 text-gray-800'}>
        {type === 'revenue' ? 'Receita' : type === 'expense' ? 'Despesa' : 'Lucro'}
      </Badge>
    );
  };

  const handleExport = () => {
    router.get(route('dres.download-export', { dre: dre.id }), { format: selectedFormat });
  };

  const handleDelete = async () => {
    setIsDeleting(true);
    
    try {
      await router.delete(route('dres.destroy', { dre: dre.id }), {
        onSuccess: () => {
          router.visit(route('dres.index'));
        },
        onError: () => {
          setIsDeleting(false);
          setShowDeleteConfirm(false);
        },
      });
    } catch (error) {
      console.error('Error deleting DRE:', error);
      setIsDeleting(false);
      setShowDeleteConfirm(false);
    }
  };

  const getIndentClass = (level: number) => {
    const indentClasses: Record<number, string> = {
      1: 'pl-0',
      2: 'pl-4',
      3: 'pl-8',
      4: 'pl-12',
      5: 'pl-16',
    };
    
    return indentClasses[level] || 'pl-0';
  };

  const getFontWeightClass = (level: number) => {
    return level === 1 ? 'font-bold' : level === 2 ? 'font-semibold' : 'font-normal';
  };

  const getLineColorClass = (type: string, amount: string) => {
    if (type === 'revenue') return 'text-green-600';
    if (type === 'expense') return 'text-red-600';
    
    // Para lucro, verifica se é positivo ou negativo
    const amountNum = parseFloat(amount);
    return amountNum >= 0 ? 'text-green-600' : 'text-red-600';
  };

  const calculateMargins = () => {
    const revenue = parseFloat(dre.totals.revenue.amount);
    const grossProfit = parseFloat(dre.totals.gross_profit.amount);
    const operatingProfit = parseFloat(dre.totals.operating_profit.amount);
    const netProfit = parseFloat(dre.totals.net_profit.amount);

    return {
      grossMargin: revenue > 0 ? (grossProfit / revenue) * 100 : 0,
      operatingMargin: revenue > 0 ? (operatingProfit / revenue) * 100 : 0,
      netMargin: revenue > 0 ? (netProfit / revenue) * 100 : 0,
    };
  };

  const margins = calculateMargins();

  return (
    <>
      <Head title={dre.title} />

      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => router.visit(route('dres.index'))}
            >
              <ArrowLeft className="mr-2 h-4 w-4" />
              Voltar
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">{dre.title}</h1>
              <div className="flex items-center gap-2 mt-1">
                <Badge variant="outline">
                  {dre.period.formatted}
                </Badge>
                {getScenarioBadge(dre.scenario)}
                {dre.generated_at && (
                  <span className="text-sm text-muted-foreground">
                    Gerado em {formatDate(dre.generated_at, 'dd/MM/yyyy HH:mm')}
                  </span>
                )}
              </div>
            </div>
          </div>
          
          <div className="flex items-center gap-2">
            <div className="flex items-center gap-2">
              <Select value={selectedFormat} onValueChange={setSelectedFormat}>
                <SelectTrigger className="w-32">
                  <SelectValue placeholder="Formato" />
                </SelectTrigger>
                <SelectContent>
                  {exportFormats.map((format) => (
                    <SelectItem key={format.value} value={format.value}>
                      {format.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Button onClick={handleExport}>
                <Download className="mr-2 h-4 w-4" />
                Exportar
              </Button>
            </div>
            
            <Button
              variant="outline"
              onClick={() => router.visit(route('dres.edit', { dre: dre.id }))}
            >
              <Edit className="mr-2 h-4 w-4" />
              Editar
            </Button>
            
            <Button
              variant="destructive"
              onClick={() => setShowDeleteConfirm(true)}
              disabled={isDeleting}
            >
              <Trash2 className="mr-2 h-4 w-4" />
              Excluir
            </Button>
          </div>
        </div>

        {showDeleteConfirm && (
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription className="flex items-center justify-between">
              <span>Tem certeza que deseja excluir este DRE? Esta ação não pode ser desfeita.</span>
              <div className="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setShowDeleteConfirm(false)}
                  disabled={isDeleting}
                >
                  Cancelar
                </Button>
                <Button
                  variant="destructive"
                  size="sm"
                  onClick={handleDelete}
                  disabled={isDeleting}
                >
                  {isDeleting ? 'Excluindo...' : 'Excluir'}
                </Button>
              </div>
            </AlertDescription>
          </Alert>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <BarChart3 className="h-5 w-5" />
                  Demonstrativo de Resultados
                </CardTitle>
                <CardDescription>
                  Detalhamento das receitas, despesas e lucros
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead className="w-20">Código</TableHead>
                      <TableHead>Descrição</TableHead>
                      <TableHead className="text-right">Valor</TableHead>
                      <TableHead className="w-24">Tipo</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {dre.lines.map((line) => (
                      <TableRow key={line.id}>
                        <TableCell className={`font-mono ${getFontWeightClass(line.level)}`}>
                          {line.code}
                        </TableCell>
                        <TableCell className={`${getIndentClass(line.level)} ${getFontWeightClass(line.level)}`}>
                          {line.description}
                          {line.notes && (
                            <p className="text-xs text-muted-foreground mt-1">{line.notes}</p>
                          )}
                        </TableCell>
                        <TableCell className={`text-right ${getFontWeightClass(line.level)} ${getLineColorClass(line.type, line.amount)}`}>
                          {line.formatted_amount}
                        </TableCell>
                        <TableCell>
                          {getTypeBadge(line.type)}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </div>

          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Resumo Financeiro</CardTitle>
                <CardDescription>
                  Totais e margens do período
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">Receita Total</span>
                    <span className="font-bold text-green-600">
                      {dre.totals.revenue.formatted}
                    </span>
                  </div>
                  
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">Despesas Totais</span>
                    <span className="font-bold text-red-600">
                      {dre.totals.expenses.formatted}
                    </span>
                  </div>
                  
                  <div className="border-t pt-2">
                    <div className="flex justify-between items-center">
                      <span className="text-sm font-medium">Lucro Líquido</span>
                      <span className={`font-bold ${
                        parseFloat(dre.totals.net_profit.amount) >= 0 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {dre.totals.net_profit.formatted}
                      </span>
                    </div>
                  </div>
                </div>

                <div className="space-y-2">
                  <h4 className="text-sm font-medium">Margens</h4>
                  
                  <div className="space-y-1">
                    <div className="flex justify-between items-center">
                      <span className="text-xs">Margem Bruta</span>
                      <span className={`text-xs font-medium ${
                        margins.grossMargin >= 0 ? 'text-green-600' : 'text-red-600'
                      }`}>
                        {margins.grossMargin.toFixed(2)}%
                      </span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-xs">Margem Operacional</span>
                      <span className={`text-xs font-medium ${
                        margins.operatingMargin >= 0 ? 'text-green-600' : 'text-red-600'
                      }`}>
                        {margins.operatingMargin.toFixed(2)}%
                      </span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-xs">Margem Líquida</span>
                      <span className={`text-xs font-medium ${
                        margins.netMargin >= 0 ? 'text-green-600' : 'text-red-600'
                      }`}>
                        {margins.netMargin.toFixed(2)}%
                      </span>
                    </div>
                  </div>
                </div>

                <div className="space-y-2">
                  <h4 className="text-sm font-medium">Outros Lucros</h4>
                  
                  <div className="space-y-1">
                    <div className="flex justify-between items-center">
                      <span className="text-xs">Lucro Bruto</span>
                      <span className={`text-xs font-medium ${
                        parseFloat(dre.totals.gross_profit.amount) >= 0 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {dre.totals.gross_profit.formatted}
                      </span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-xs">Lucro Operacional</span>
                      <span className={`text-xs font-medium ${
                        parseFloat(dre.totals.operating_profit.amount) >= 0 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {dre.totals.operating_profit.formatted}
                      </span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-xs">EBITDA</span>
                      <span className={`text-xs font-medium ${
                        parseFloat(dre.totals.ebitda.amount) >= 0 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {dre.totals.ebitda.formatted}
                      </span>
                    </div>
                    
                    <div className="flex justify-between items-center">
                      <span className="text-xs">EBIT</span>
                      <span className={`text-xs font-medium ${
                        parseFloat(dre.totals.ebit.amount) >= 0 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {dre.totals.ebit.formatted}
                      </span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Ações Rápidas</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.visit(route('dres.generate-consolidated'))}
                >
                  <BarChart3 className="mr-2 h-4 w-4" />
                  Gerar Consolidado
                </Button>
                
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.visit(route('dres.generate-comparative'))}
                >
                  <BarChart3 className="mr-2 h-4 w-4" />
                  Gerar Comparativo
                </Button>
                
                <Button
                  variant="outline"
                  className="w-full justify-start"
                  onClick={() => router.visit(route('dres.ratios', { dre: dre.id }))}
                >
                  <BarChart3 className="mr-2 h-4 w-4" />
                  Ver Índices Financeiros
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </>
  );
}