import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';
import { ArrowLeft, Calendar, DollarSign, Edit, FileText, PieChart, Trash2 } from 'lucide-react';
import { PageProps } from '@/types';
import { formatBRL } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';

interface ShowProps extends PageProps {
    projection: {
        id: string;
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
        created_at: string;
        updated_at: string;
    };
}

export default function Show({ auth, projection }: ShowProps) {
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

    const getScenarioColor = (scenario: string) => {
        const colors: Record<string, string> = {
            base: 'bg-blue-500',
            optimistic: 'bg-green-500',
            pessimistic: 'bg-red-500',
            custom: 'bg-purple-500',
        };
        return colors[scenario] || 'bg-gray-500';
    };

    const getTypeColor = (type: string) => {
        const colors: Record<string, string> = {
            revenue: 'bg-green-500',
            expense: 'bg-red-500',
            profit: 'bg-blue-500',
            cash_flow: 'bg-purple-500',
            balance_sheet: 'bg-amber-500',
        };
        return colors[type] || 'bg-gray-500';
    };

    return (
        <AuthenticatedLayout
        >
            <Head title={projection.title} />
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="icon" asChild>
                        <Link href={route('financial-projections.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">{projection.title}</h2>
                        <p className="text-muted-foreground">
                            Detalhes da projeção financeira
                        </p>
                    </div>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href={route('financial-projections.edit', { projectionId: projection.id })}>
                            <Edit className="h-4 w-4 mr-2" />
                            Editar
                        </Link>
                    </Button>
                    <Button variant="destructive" asChild>
                        <Link
                            href={route('financial-projections.destroy', { projectionId: projection.id })}
                            method="delete"
                            as="button"
                            onBefore={() => {
                                return confirm('Tem certeza que deseja excluir esta projeção?');
                            }}
                        >
                            <Trash2 className="h-4 w-4 mr-2" />
                            Excluir
                        </Link>
                    </Button>
                </div>
            </div>
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
                                <div className="flex items-center gap-2">
                                    <Badge className={getTypeColor(projection.type)}>
                                        {getTypeLabel(projection.type)}
                                    </Badge>
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
                                <div className="flex items-center gap-2">
                                    <Badge className={getScenarioColor(projection.scenario)}>
                                        {getScenarioLabel(projection.scenario)}
                                    </Badge>
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
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Calendar className="h-4 w-4" />
                                        <span>Período</span>
                                    </div>
                                    <p className="font-medium">{getPeriodLabel()}</p>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <PieChart className="h-4 w-4" />
                                        <span>Tipo de Período</span>
                                    </div>
                                    <p className="font-medium">
                                        {projection.period_type === 'monthly' ? 'Mensal' :
                                            projection.period_type === 'quarterly' ? 'Trimestral' : 'Anual'}
                                    </p>
                                </div>
                            </div>

                            {projection.notes && (
                                <div className="space-y-2">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <FileText className="h-4 w-4" />
                                        <span>Observações</span>
                                    </div>
                                    <p className="text-sm whitespace-pre-wrap">{projection.notes}</p>
                                </div>
                            )}

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                <div className="space-y-2">
                                    <div className="text-sm text-muted-foreground">Criado em</div>
                                    <p className="text-sm">{formatDate(projection.created_at)}</p>
                                </div>
                                <div className="space-y-2">
                                    <div className="text-sm text-muted-foreground">Atualizado em</div>
                                    <p className="text-sm">{formatDate(projection.updated_at)}</p>
                                </div>
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
                                <div className="text-sm text-muted-foreground">
                                    Total: {formatBRL(projection.total)}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {projection.items.map((item) => (
                                    <div key={item.id} className="border rounded-lg p-4">
                                        <div className="flex items-start justify-between">
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <h4 className="font-medium">{item.description}</h4>
                                                    {item.category_name && (
                                                        <Badge variant="outline" className="text-xs">
                                                            {item.category_name}
                                                        </Badge>
                                                    )}
                                                </div>
                                                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                                    <span className="flex items-center gap-1">
                                                        <Calendar className="h-3 w-3" />
                                                        {formatDate(item.date)}
                                                    </span>
                                                    {item.source && (
                                                        <span className="flex items-center gap-1">
                                                            <FileText className="h-3 w-3" />
                                                            Fonte: {item.source}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-lg font-bold">
                                                    {formatBRL(item.amount)}
                                                </div>
                                            </div>
                                        </div>

                                        {item.notes && (
                                            <>
                                                <Separator className="my-3" />
                                                <div className="text-sm text-muted-foreground">
                                                    <p className="whitespace-pre-wrap">{item.notes}</p>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}