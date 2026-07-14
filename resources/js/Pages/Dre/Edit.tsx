import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { formatCurrency } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { ArrowLeft, Save, AlertCircle } from 'lucide-react';

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
  };
  generated_at: string | null;
}

interface Category {
  id: string;
  name: string;
  code: string;
}

interface Props {
  dre: Dre;
  categories: Category[];
  scenarios: Array<{ value: string; label: string }>;
}

const periodTypes = [
  { value: 'monthly', label: 'Mensal' },
  { value: 'quarterly', label: 'Trimestral' },
  { value: 'yearly', label: 'Anual' },
  { value: 'custom', label: 'Customizado' },
];

export default function DreEdit({ dre, categories, scenarios }: Props) {
  const [formData, setFormData] = useState({
    period_type: dre.period.type,
    year_month: dre.period.type === 'monthly' 
      ? new Date(dre.period.start).toISOString().slice(0, 7) 
      : new Date().toISOString().slice(0, 7),
    year: dre.period.type === 'yearly' || dre.period.type === 'quarterly'
      ? new Date(dre.period.start).getFullYear().toString()
      : new Date().getFullYear().toString(),
    quarter: dre.period.type === 'quarterly'
      ? Math.ceil((new Date(dre.period.start).getMonth() + 1) / 3)
      : 1,
    start_date: dre.period.type === 'custom'
      ? new Date(dre.period.start).toISOString().slice(0, 10)
      : '',
    end_date: dre.period.type === 'custom'
      ? new Date(dre.period.end).toISOString().slice(0, 10)
      : '',
    category_id: dre.category_id || '',
    scenario: dre.scenario,
  });

  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  useEffect(() => {
    if (isSuccess) {
      const timer = setTimeout(() => {
        setIsSuccess(false);
      }, 3000);
      
      return () => clearTimeout(timer);
    }
  }, [isSuccess]);

  const handleChange = (field: string, value: any) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    
    // Limpar erro quando o campo é alterado
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.period_type) {
      newErrors.period_type = 'O tipo de período é obrigatório.';
    }

    if (formData.period_type === 'monthly' && !formData.year_month) {
      newErrors.year_month = 'O mês/ano é obrigatório para período mensal.';
    }

    if (formData.period_type === 'quarterly') {
      if (!formData.year) {
        newErrors.year = 'O ano é obrigatório para período trimestral.';
      }
      if (!formData.quarter || formData.quarter < 1 || formData.quarter > 4) {
        newErrors.quarter = 'O trimestre deve ser entre 1 e 4.';
      }
    }

    if (formData.period_type === 'yearly' && !formData.year) {
      newErrors.year = 'O ano é obrigatório para período anual.';
    }

    if (formData.period_type === 'custom') {
      if (!formData.start_date) {
        newErrors.start_date = 'A data inicial é obrigatória.';
      }
      if (!formData.end_date) {
        newErrors.end_date = 'A data final é obrigatória.';
      }
      if (formData.start_date && formData.end_date && formData.start_date > formData.end_date) {
        newErrors.end_date = 'A data final deve ser igual ou posterior à data inicial.';
      }
    }

    return newErrors;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    const validationErrors = validateForm();
    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setIsSubmitting(true);

    try {
      await router.put(route('dres.update', { dre: dre.id }), formData, {
        onSuccess: () => {
          setIsSuccess(true);
          setIsSubmitting(false);
        },
        onError: (errors) => {
          setErrors(errors as Record<string, string>);
          setIsSubmitting(false);
        },
      });
    } catch (error) {
      console.error('Error updating DRE:', error);
      setIsSubmitting(false);
    }
  };

  const getQuarterMonths = (quarter: number) => {
    const months = [
      ['Janeiro', 'Fevereiro', 'Março'],
      ['Abril', 'Maio', 'Junho'],
      ['Julho', 'Agosto', 'Setembro'],
      ['Outubro', 'Novembro', 'Dezembro'],
    ];
    return months[quarter - 1]?.join(', ') || '';
  };

  return (
    <>
      <Head title={`Editar DRE - ${dre.title}`} />

      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="sm"
            onClick={() => router.visit(route('dres.show', { dre: dre.id }))}
          >
            <ArrowLeft className="mr-2 h-4 w-4" />
            Voltar
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Editar DRE</h1>
            <p className="text-muted-foreground">
              Atualize as configurações do Demonstrativo de Resultados
            </p>
          </div>
        </div>

        {isSuccess && (
          <Alert className="bg-green-50 border-green-200">
            <AlertCircle className="h-4 w-4 text-green-600" />
            <AlertDescription className="text-green-800">
              DRE atualizado com sucesso!
            </AlertDescription>
          </Alert>
        )}

        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 space-y-6">
              <Card>
                <CardHeader>
                  <CardTitle>Configuração do Período</CardTitle>
                  <CardDescription>
                    Defina o período de análise para o DRE
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="period_type">Tipo de Período *</Label>
                    <Select
                      value={formData.period_type}
                      onValueChange={(value) => handleChange('period_type', value)}
                    >
                      <SelectTrigger className={errors.period_type ? 'border-red-500' : ''}>
                        <SelectValue placeholder="Selecione o tipo de período" />
                      </SelectTrigger>
                      <SelectContent>
                        {periodTypes.map((type) => (
                          <SelectItem key={type.value} value={type.value}>
                            {type.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.period_type && (
                      <p className="text-sm text-red-500">{errors.period_type}</p>
                    )}
                  </div>

                  {formData.period_type === 'monthly' && (
                    <div className="space-y-2">
                      <Label htmlFor="year_month">Mês/Ano *</Label>
                      <Input
                        type="month"
                        id="year_month"
                        value={formData.year_month}
                        onChange={(e) => handleChange('year_month', e.target.value)}
                        className={errors.year_month ? 'border-red-500' : ''}
                      />
                      {errors.year_month && (
                        <p className="text-sm text-red-500">{errors.year_month}</p>
                      )}
                    </div>
                  )}

                  {formData.period_type === 'quarterly' && (
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="year">Ano *</Label>
                        <Input
                          type="number"
                          id="year"
                          value={formData.year}
                          onChange={(e) => handleChange('year', e.target.value)}
                          min="2000"
                          max="2100"
                          className={errors.year ? 'border-red-500' : ''}
                        />
                        {errors.year && (
                          <p className="text-sm text-red-500">{errors.year}</p>
                        )}
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="quarter">Trimestre *</Label>
                        <Select
                          value={formData.quarter.toString()}
                          onValueChange={(value) => handleChange('quarter', parseInt(value))}
                        >
                          <SelectTrigger className={errors.quarter ? 'border-red-500' : ''}>
                            <SelectValue placeholder="Selecione o trimestre" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="1">1º Trimestre</SelectItem>
                            <SelectItem value="2">2º Trimestre</SelectItem>
                            <SelectItem value="3">3º Trimestre</SelectItem>
                            <SelectItem value="4">4º Trimestre</SelectItem>
                          </SelectContent>
                        </Select>
                        {errors.quarter && (
                          <p className="text-sm text-red-500">{errors.quarter}</p>
                        )}
                        {formData.quarter > 0 && (
                          <p className="text-xs text-muted-foreground">
                            Meses: {getQuarterMonths(formData.quarter)}
                          </p>
                        )}
                      </div>
                    </div>
                  )}

                  {formData.period_type === 'yearly' && (
                    <div className="space-y-2">
                      <Label htmlFor="year">Ano *</Label>
                      <Input
                        type="number"
                        id="year"
                        value={formData.year}
                        onChange={(e) => handleChange('year', e.target.value)}
                        min="2000"
                        max="2100"
                        className={errors.year ? 'border-red-500' : ''}
                      />
                      {errors.year && (
                        <p className="text-sm text-red-500">{errors.year}</p>
                      )}
                    </div>
                  )}

                  {formData.period_type === 'custom' && (
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="start_date">Data Inicial *</Label>
                        <Input
                          type="date"
                          id="start_date"
                          value={formData.start_date}
                          onChange={(e) => handleChange('start_date', e.target.value)}
                          className={errors.start_date ? 'border-red-500' : ''}
                        />
                        {errors.start_date && (
                          <p className="text-sm text-red-500">{errors.start_date}</p>
                        )}
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="end_date">Data Final *</Label>
                        <Input
                          type="date"
                          id="end_date"
                          value={formData.end_date}
                          onChange={(e) => handleChange('end_date', e.target.value)}
                          className={errors.end_date ? 'border-red-500' : ''}
                        />
                        {errors.end_date && (
                          <p className="text-sm text-red-foreground">{errors.end_date}</p>
                        )}
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Filtros e Cenário</CardTitle>
                  <CardDescription>
                    Aplique filtros e selecione o cenário de análise
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="category_id">Categoria</Label>
                    <Select
                      value={formData.category_id}
                      onValueChange={(value) => handleChange('category_id', value)}
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
                    <p className="text-xs text-muted-foreground">
                      Deixe em branco para incluir todas as categorias
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="scenario">Cenário</Label>
                    <Select
                      value={formData.scenario}
                      onValueChange={(value) => handleChange('scenario', value)}
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
                    <p className="text-xs text-muted-foreground">
                      Cenário base: dados reais. Outros cenários: projeções e simulações.
                    </p>
                  </div>
                </CardContent>
              </Card>
            </div>

            <div className="space-y-6">
              <Card>
                <CardHeader>
                  <CardTitle>Resumo</CardTitle>
                  <CardDescription>
                    Confira as configurações antes de atualizar
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Tipo de Período</Label>
                    <p className="text-sm">
                      {periodTypes.find(pt => pt.value === formData.period_type)?.label || 'N/A'}
                    </p>
                  </div>

                  {formData.period_type === 'monthly' && (
                    <div className="space-y-2">
                      <Label className="text-sm font-medium">Período</Label>
                      <p className="text-sm">
                        {new Date(formData.year_month + '-01').toLocaleDateString('pt-BR', { 
                          month: 'long', 
                          year: 'numeric' 
                        })}
                      </p>
                    </div>
                  )}

                  {formData.period_type === 'quarterly' && (
                    <div className="space-y-2">
                      <Label className="text-sm font-medium">Período</Label>
                      <p className="text-sm">
                        {formData.quarter}º Trimestre de {formData.year}
                      </p>
                    </div>
                  )}

                  {formData.period_type === 'yearly' && (
                    <div className="space-y-2">
                      <Label className="text-sm font-medium">Período</Label>
                      <p className="text-sm">Ano {formData.year}</p>
                    </div>
                  )}

                  {formData.period_type === 'custom' && formData.start_date && formData.end_date && (
                    <div className="space-y-2">
                      <Label className="text-sm font-medium">Período</Label>
                      <p className="text-sm">
                        {new Date(formData.start_date).toLocaleDateString('pt-BR')} a{' '}
                        {new Date(formData.end_date).toLocaleDateString('pt-BR')}
                      </p>
                    </div>
                  )}

                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Categoria</Label>
                    <p className="text-sm">
                      {formData.category_id 
                        ? categories.find(c => c.id === formData.category_id)?.name || 'N/A'
                        : 'Todas as categorias'}
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Cenário</Label>
                    <p className="text-sm">
                      {scenarios.find(s => s.value === formData.scenario)?.label || 'N/A'}
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label className="text-sm font-medium">DRE Original</Label>
                    <div className="text-xs space-y-1">
                      <p><strong>Título:</strong> {dre.title}</p>
                      <p><strong>Período:</strong> {dre.period.formatted}</p>
                      <p><strong>Receita:</strong> {dre.totals.revenue.formatted}</p>
                      <p><strong>Lucro:</strong> {dre.totals.net_profit.formatted}</p>
                      {dre.generated_at && (
                        <p><strong>Gerado em:</strong> {formatDate(dre.generated_at, 'dd/MM/yyyy HH:mm')}</p>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Alert>
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>
                  Ao atualizar, um novo DRE será gerado com base nas configurações selecionadas.
                  O DRE original será substituído.
                </AlertDescription>
              </Alert>

              <div className="flex flex-col gap-2">
                <Button 
                  type="submit" 
                  className="w-full"
                  disabled={isSubmitting}
                >
                  {isSubmitting ? 'Atualizando...' : (
                    <>
                      <Save className="mr-2 h-4 w-4" />
                      Atualizar DRE
                    </>
                  )}
                </Button>
                
                <Button
                  type="button"
                  variant="outline"
                  className="w-full"
                  onClick={() => router.visit(route('dres.show', { dre: dre.id }))}
                  disabled={isSubmitting}
                >
                  Cancelar
                </Button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </>
  );
}