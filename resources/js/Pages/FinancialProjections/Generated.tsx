import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, Download, Save, Share2 } from 'lucide-react';
import { PageProps } from '@/types';
import { formatBRL } from '@/Utils/formatCurrency';
import { useState } from 'react';

interface GeneratedProps extends PageProps {
    projection: {
        id?: string;
        type: 'revenue' | 'expense' | 'profit' | 'cash_flow' | 'balance_sheet';
        period_type: 'monthly' | 'quarterly' | 'yearly';
        year_month?: string;
        year?: string;
        quarter?: number;
        scenario: 'base' | 'optimistic' | 'pessimistic' | 'custom';
        title: string;
        notes?: string;
        total: string;
        items: Array<{
            id: string;
            date: string;
            description: string;
            amount: string;
            category_name?: string;
            notes?: string;
            source?: string;
        }>;
        generated_at: string;
        is_saved: boolean;
    };
    categories: Array<{
        id: string;
        name: string;
        type: 'revenue' | 'expense';
    }>;
}

export default function Generated({ auth, projection, categories }: GeneratedProps) {
    const { data, setData, post, processing, errors } = useForm({
        title: projection.title,
        notes: projection.notes || '',
        scenario: projection.scenario,
    });

    const [isSaving, setIsSaving] = useState(false);

    const handleSave = () => {
        setIsSaving(true);
        post(route('financial-projections.store'), {
            data: {
                ...projection,
                ...data,
                items: projection.items.map(item => ({
                    ...item,
                    amount: parseFloat(item.amount) || 0,
                })),
            },
            onSuccess: () => {
                setIsSaving(false);
            },
            onError: () => {
                setIsSaving(false);
            },
        });
    };

    const getTypeLabel = (type: string) => {
        const labels: Record<string, string> = {
            revenue: 'Receita',
            expense: 'Despesa',
            profit: 'Lucro',
            cash_flow: 'Fluxo de Caixa',
            balance_sheet: 'Balanço Patrimonial',
        };
        return labels[type] || type;
    };

    const getScenarioLabel = (scenario: string) => {
        const labels: Record<string, string> = {
            base: 'Base',
            optimistic: 'Otimista',
            pessimistic: 'Pessimista',
            custom: 'Personalizado',
        };
        return labels[scenario] || scenario;
    };

    const getPeriodLabel = () => {
        if (projection.period_type === 'monthly' && projection.year_month) {
            const [year, month] = projection.year_month.split('-');
            const monthNames = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];
            return `${monthNames[parseInt(month) - 1]} de ${year}`;
        }
        
        if (projection.period_type === 'quarterly' && projection.year && projection.quarter) {
            return `${projection.quarter}º Trimestre de ${projection.year}`;
        }
        
        if (projection.period_type === 'yearly' && projection.year) {
            return `Ano ${projection.year}`;
        }
        
        return 'Período não especificado';
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    };

    const exportToCSV = () => {
        const headers = ['Data', 'Descrição', 'Categoria', 'Valor (R$)', 'Observações'];
        const rows = projection.items.map(item => [
            formatDate(item.date),
            item.description,
            item.category_name || 'Sem categoria',
            parseFloat(item.amount).toFixed(2).replace('.', ','),
            item.notes || '',
        ]);

        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.join(',')),
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `projecao-${projection.type}-${getPeriodLabel().toLowerCase().replace(/ /g, '-')}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="icon" asChild>
                            <Link href={route('financial-projections.index')}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">Projeção Gerada</h2>
                            <p className="text-muted-foreground">
                                {getTypeLabel(projection.type)} - {getPeriodLabel()}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={exportToCSV}>
                            <Download className="h-4 w-4 mr-2" />
                            Exportar CSV
                        </Button>
                        {!projection.is_saved && (
                            <Button onClick={handleSave} disabled={isSaving}>
                                <Save className="h-4 w-4 mr-2" />
                                {isSaving ? 'Salvando...' : 'Salvar Projeção'}
                            </Button>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`Projeção Gerada: ${projection.title}`} />

            <div className="max-w-6xl mx-auto">
                <div className="grid gap-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Tipo de Projeção
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-lg font-semibold">
                                    {getTypeLabel(projection.type)}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Cenário
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-lg font-semibold">
                                    {getScenarioLabel(projection.scenario)}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Valor Total
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {formatBRL(projection.total)}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Informações da Projeção</CardTitle>
                            <CardDescription>
                                Detalhes sobre período e configurações
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <div className="text-sm text-muted-foreground">Período</div>
                                    <p className="font-medium">{getPeriodLabel()}</p>
                                </div>

                                <div className="space-y-2">
                                    <div className="text-sm text-muted-foreground">Tipo de Período</div>
                                    <p className="font-medium">
                                        {projection.period_type === 'monthly' ? 'Mensal' :
                                         projection.period_type === 'quarterly' ? 'Trimestral' : 'Anual'}
                                    </p>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="title">Título</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="Ex: Projeção de Receitas 2024"
                                />
                                {errors.title && (
                                    <p className="text-sm text-destructive">{errors.title}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="notes">Observações</Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Observações adicionais sobre esta projeção"
                                    rows={3}
                                />
                                {errors.notes && (
                                    <p className="text-sm text-destructive">{errors.notes}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="scenario">Cenário</Label>
                                <Select
                                    value={data.scenario}
                                    onValueChange={(value: any) => setData('scenario', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione o cenário" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="base">Base</SelectItem>
                                        <SelectItem value="optimistic">Otimista</SelectItem>
                                        <SelectItem value="pessimistic">Pessimista</SelectItem>
                                        <SelectItem value="custom">Personalizado</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.scenario && (
                                    <p className="text-sm text-destructive">{errors.scenario}</p>
                                )}
                            </div>

                            <div className="text-sm text-muted-foreground">
                                Gerado em: {formatDate(projection.generated_at)}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>Itens da Projeção</CardTitle>
                                    <CardDescription>
                                        {projection.items.length} itens que compõem esta projeção
                                    </CardDescription>
                                </div>
                                <div className="text-lg font-bold">
                                    Total: {formatBRL(projection.total)}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                                                Data
                                            </th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                                                Descrição
                                            </th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                                                Categoria
                                            </th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                                                Valor
                                            </th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                                                Observações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {projection.items.map((item) => (
                                            <tr key={item.id} className="border-b hover:bg-muted/50">
                                                <td className="py-3 px-4">
                                                    <div className="text-sm font-medium">
                                                        {formatDate(item.date)}
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="font-medium">{item.description}</div>
                                                    {item.source && (
                                                        <div className="text-xs text-muted-foreground">
                                                            Fonte: {item.source}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="text-sm">
                                                        {item.category_name || 'Sem categoria'}
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="font-bold">
                                                        {formatBRL(item.amount)}
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="text-sm text-muted-foreground max-w-xs truncate">
                                                        {item.notes || '-'}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="bg-muted/50">
                                            <td colSpan={3} className="py-3 px-4 text-right font-bold">
                                                Total:
                                            </td>
                                            <td className="py-3 px-4 font-bold text-lg">
                                                {formatBRL(projection.total)}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('financial-projections.index')}>
                                Voltar para Lista
                            </Link>
                        </Button>
                        {!projection.is_saved && (
                            <Button onClick={handleSave} disabled={isSaving}>
                                <Save className="h-4 w-4 mr-2" />
                                {isSaving ? 'Salvando...' : 'Salvar Projeção'}
                            </Button>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}