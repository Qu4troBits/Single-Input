import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { formatCurrency } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { Search, Plus, Download, BarChart3, Filter } from 'lucide-react';

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
  };
  generated_at: string;
}

interface Category {
  id: string;
  name: string;
  code: string;
}

interface Props {
  dres: {
    data: Dre[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  filters: {
    period_type: string;
    year_month: string;
    year: string;
    quarter: number;
    category_id: string | null;
    scenario: string;
  };
  categories: Category[];
}

const periodTypes = [
  { value: 'monthly', label: 'Mensal' },
  { value: 'quarterly', label: 'Trimestral' },
  { value: 'yearly', label: 'Anual' },
  { value: 'custom', label: 'Customizado' },
];

const scenarios = [
  { value: 'base', label: 'Base' },
  { value: 'optimistic', label: 'Otimista' },
  { value: 'pessimistic', label: 'Pessimista' },
  { value: 'conservative', label: 'Conservador' },
];

export default function DreIndex({ dres, filters, categories }: Props) {
  const [localFilters, setLocalFilters] = useState(filters);
  const [isFiltering, setIsFiltering] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      if (isFiltering) {
        router.get(route('dres.index'), localFilters, {
          preserveState: true,
          preserveScroll: true,
        });
        setIsFiltering(false);
      }
    }, 500);

    return () => clearTimeout(timer);
  }, [localFilters, isFiltering]);

  const handleFilterChange = (key: string, value: any) => {
    setLocalFilters(prev => ({ ...prev, [key]: value }));
    setIsFiltering(true);
  };

  const handleExport = (dreId: string, format: string) => {
    router.get(route('dres.download-export', { dre: dreId }), { format });
  };

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

  const getPeriodTypeLabel = (type: string) => {
    return periodTypes.find(pt => pt.value === type)?.label || type;
  };

  return (
    <>
      <Head title="DRE - Demonstrativo de Resultados" />

      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Demonstrativo de Resultados (DRE)</h1>
            <p className="text-muted-foreground">
              Análise detalhada da performance financeira da empresa
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.get(route('dres.create'))}>
              <Plus className="mr-2 h-4 w-4" />
              Novo DRE
            </Button>
            <Button variant="outline" onClick={() => router.get(route('dres.consolidated'))}>
              <BarChart3 className="mr-2 h-4 w-4" />
              Consolidado
            </Button>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filtros
            </CardTitle>
            <CardDescription>
              Filtre os DREs por período, categoria e cenário
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="space-y-2">
                <Label htmlFor="period_type">Tipo de Período</Label>
                <Select
                  value={localFilters.period_type}
                  onValueChange={(value) => handleFilterChange('period_type', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione o tipo" />
                  </SelectTrigger>
                  <SelectContent>
                    {periodTypes.map((type) => (
                      <SelectItem key={type.value} value={type.value}>
                        {type.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              {localFilters.period_type === 'monthly' && (
                <div className="space-y-2">
                  <Label htmlFor="year_month">Mês/Ano</Label>
                  <Input
                    type="month"
                    id="year_month"
                    value={localFilters.year_month}
                    onChange={(e) => handleFilterChange('year_month', e.target.value)}
                  />
                </div>
              )}

              {localFilters.period_type === 'quarterly' && (
                <>
                  <div className="space-y-2">
                    <Label htmlFor="year">Ano</Label>
                    <Input
                      type="number"
                      id="year"
                      value={localFilters.year}
                      onChange={(e) => handleFilterChange('year', e.target.value)}
                      min="2000"
                      max="2100"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="quarter">Trimestre</Label>
                    <Select
                      value={localFilters.quarter.toString()}
                      onValueChange={(value) => handleFilterChange('quarter', parseInt(value))}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Selecione" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1">1º Trimestre</SelectItem>
                        <SelectItem value="2">2º Trimestre</SelectItem>
                        <SelectItem value="3">3º Trimestre</SelectItem>
                        <SelectItem value="4">4º Trimestre</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </>
              )}

              {localFilters.period_type === 'yearly' && (
                <div className="space-y-2">
                  <Label htmlFor="year">Ano</Label>
                  <Input
                    type="number"
                    id="year"
                    value={localFilters.year}
                    onChange={(e) => handleFilterChange('year', e.target.value)}
                    min="2000"
                    max="2100"
                  />
                </div>
              )}

              <div className="space-y-2">
                <Label htmlFor="category_id">Categoria</Label>
                <Select
                  value={localFilters.category_id || ''}
                  onValueChange={(value) => handleFilterChange('category_id', value || null)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Todas as categorias" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Todas as categorias</SelectItem>
                    {categories.map((category) => (
                      <SelectItem key={category.id} value={category.id}>
                        {category.name} ({category.code})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="scenario">Cenário</Label>
                <Select
                  value={localFilters.scenario}
                  onValueChange={(value) => handleFilterChange('scenario', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione o cenário" />
                  </SelectTrigger>
                  <SelectContent>
                    {scenarios.map((scenario) => (
                      <SelectItem key={scenario.value} value={scenario.value}>
                        {scenario.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>DREs Gerados</CardTitle>
            <CardDescription>
              {dres.meta.total} DREs encontrados
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Título</TableHead>
                  <TableHead>Período</TableHead>
                  <TableHead>Categoria</TableHead>
                  <TableHead>Cenário</TableHead>
                  <TableHead>Receita</TableHead>
                  <TableHead>Despesas</TableHead>
                  <TableHead>Lucro Líquido</TableHead>
                  <TableHead>Gerado em</TableHead>
                  <TableHead className="text-right">Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {dres.data.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={9} className="text-center py-8 text-muted-foreground">
                      Nenhum DRE encontrado. Crie seu primeiro DRE!
                    </TableCell>
                  </TableRow>
                ) : (
                  dres.data.map((dre) => (
                    <TableRow key={dre.id}>
                      <TableCell className="font-medium">
                        <Link href={route('dres.show', { dre: dre.id })} className="hover:underline">
                          {dre.title}
                        </Link>
                      </TableCell>
                      <TableCell>
                        <div className="flex flex-col">
                          <span>{dre.period.formatted}</span>
                          <span className="text-xs text-muted-foreground">
                            {getPeriodTypeLabel(dre.period.type)}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell>
                        {dre.category_id ? (
                          <Badge variant="outline">
                            {categories.find(c => c.id === dre.category_id)?.name || 'N/A'}
                          </Badge>
                        ) : (
                          <span className="text-muted-foreground">Todas</span>
                        )}
                      </TableCell>
                      <TableCell>{getScenarioBadge(dre.scenario)}</TableCell>
                      <TableCell className="font-medium text-green-600">
                        {dre.totals.revenue.formatted}
                      </TableCell>
                      <TableCell className="font-medium text-red-600">
                        {dre.totals.expenses.formatted}
                      </TableCell>
                      <TableCell className={`font-bold ${
                        parseFloat(dre.totals.net_profit.amount) >= 0 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {dre.totals.net_profit.formatted}
                      </TableCell>
                      <TableCell>
                        {formatDate(dre.generated_at, 'dd/MM/yyyy HH:mm')}
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex items-center justify-end gap-2">
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handleExport(dre.id, 'pdf')}
                            title="Exportar PDF"
                          >
                            <Download className="h-4 w-4" />
                          </Button>
                          <Button
                            variant="ghost"
                            size="sm"
                            asChild
                          >
                            <Link href={route('dres.edit', { dre: dre.id })}>
                              Editar
                            </Link>
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>

            {dres.meta.last_page > 1 && (
              <div className="flex items-center justify-between mt-4">
                <div className="text-sm text-muted-foreground">
                  Mostrando {dres.data.length} de {dres.meta.total} DREs
                </div>
                <div className="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => router.get(route('dres.index', { page: dres.meta.current_page > 1 ? dres.meta.current_page - 1 : 1 }), localFilters, {
                      preserveState: true,
                      preserveScroll: true,
                    })}
                    disabled={dres.meta.current_page === 1}
                  >
                    Anterior
                  </Button>
                  <span className="text-sm">
                    Página {dres.meta.current_page} de {dres.meta.last_page}
                  </span>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => router.get(route('dres.index', { page: dres.meta.current_page < dres.meta.last_page ? dres.meta.current_page + 1 : dres.meta.last_page }), localFilters, {
                      preserveState: true,
                      preserveScroll: true,
                    })}
                    disabled={dres.meta.current_page === dres.meta.last_page}
                  >
                    Próxima
                  </Button>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </>
  );
}