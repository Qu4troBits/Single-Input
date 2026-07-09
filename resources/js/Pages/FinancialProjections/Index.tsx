import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Link } from '@inertiajs/react';
import { formatBRL } from '@/Utils/formatCurrency';
import { formatDate } from '@/Utils/formatDate';
import { Category, FinancialProjection } from '@/types';

interface FinancialProjectionsIndexProps extends PageProps {
    recent_projections: FinancialProjection[];
    available_scenarios: string[];
    categories: Category[];
    current_year: string;
    current_month: string;
}

export default function Index({
    auth,
    recent_projections,
    available_scenarios,
    categories,
    current_year,
    current_month
}: FinancialProjectionsIndexProps) {
    const getProjectionTypeColor = (type: string) => {
        switch (type) {
            case 'revenue': return 'bg-green-100 text-green-800';
            case 'expense': return 'bg-red-100 text-red-800';
            case 'profit': return 'bg-blue-100 text-blue-800';
            case 'cash_flow': return 'bg-purple-100 text-purple-800';
            case 'balance_sheet': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getProjectionTypeLabel = (type: string) => {
        switch (type) {
            case 'revenue': return 'Receita';
            case 'expense': return 'Despesa';
            case 'profit': return 'Lucro';
            case 'cash_flow': return 'Fluxo de Caixa';
            case 'balance_sheet': return 'Balanço Patrimonial';
            default: return 'Desconhecido';
        }
    };

    const getPeriodTypeLabel = (type: string) => {
        switch (type) {
            case 'monthly': return 'Mensal';
            case 'quarterly': return 'Trimestral';
            case 'yearly': return 'Anual';
            default: return 'Desconhecido';
        }
    };

    return (
        <AuthenticatedLayout
        >
            <Head title="Projeções Financeiras" />
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">Projeções Financeiras</h2>
                    <p className="text-muted-foreground">
                        Planejamento e projeções financeiras para sua empresa
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button asChild>
                        <Link href={route('financial-projections.create')}>
                            Nova Projeção
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={route('financial-projections.generate')}>
                            Gerar Projeção
                        </Link>
                    </Button>
                </div>
            </div>
            <div className="space-y-6">
                {/* Resumo Rápido */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Projeções Recentes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {recent_projections.length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Último mês
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Cenários Disponíveis
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {available_scenarios.length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Base, Otimista, Pessimista
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Período Atual
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {current_month.replace('-', '/')}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {current_year}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Projeções Recentes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Projeções Recentes</CardTitle>
                        <CardDescription>
                            Últimas projeções financeiras geradas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {recent_projections.length === 0 ? (
                            <div className="text-center py-8">
                                <p className="text-muted-foreground">
                                    Nenhuma projeção encontrada. Crie sua primeira projeção!
                                </p>
                                <Button className="mt-4" asChild>
                                    <Link href={route('financial-projections.create')}>
                                        Criar Primeira Projeção
                                    </Link>
                                </Button>
                            </div>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Título</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Período</TableHead>
                                        <TableHead>Cenário</TableHead>
                                        <TableHead>Total</TableHead>
                                        <TableHead>Itens</TableHead>
                                        <TableHead>Criada em</TableHead>
                                        <TableHead>Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {recent_projections.map((projection) => (
                                        <TableRow key={projection.id}>
                                            <TableCell className="font-medium">
                                                {projection.title}
                                            </TableCell>
                                            <TableCell>
                                                <Badge className={getProjectionTypeColor(projection.type)}>
                                                    {getProjectionTypeLabel(projection.type)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    <span>{getPeriodTypeLabel(projection.period.type)}</span>
                                                    <div className="text-xs text-muted-foreground">
                                                        {projection.period.label}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {projection.scenario}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatBRL(projection.total)}
                                            </TableCell>
                                            <TableCell>
                                                <span className="font-semibold">
                                                    {projection.items.length}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-sm text-muted-foreground">
                                                    {formatDate(projection.generated_at)}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-2">
                                                    {projection.id && (
                                                        <>
                                                            <Button size="sm" variant="outline" asChild>
                                                                <Link href={route('financial-projections.show', { projection: projection.id })}>
                                                                    Ver
                                                                </Link>
                                                            </Button>
                                                            <Button size="sm" variant="outline" asChild>
                                                                <Link href={route('financial-projections.edit', { projection: projection.id })}>
                                                                    Editar
                                                                </Link>
                                                            </Button>
                                                        </>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>

                {/* Ferramentas de Projeção */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Gerar Projeção Automática</CardTitle>
                            <CardDescription>
                                Gere projeções baseadas em dados históricos
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground mb-4">
                                O sistema analisa seus dados históricos e aplica fórmulas de crescimento para gerar projeções automáticas.
                            </p>
                            <ul className="space-y-2 text-sm mb-4">
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Análise de tendências históricas
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Fatores de crescimento ajustáveis
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Múltiplos cenários (base, otimista, pessimista)
                                </li>
                            </ul>
                            <Button asChild>
                                <Link href={route('financial-projections.generate')}>
                                    Gerar Projeção Automática
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Projeção Manual</CardTitle>
                            <CardDescription>
                                Crie projeções personalizadas manualmente
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground mb-4">
                                Para cenários específicos ou ajustes finos, crie projeções manualmente.
                            </p>
                            <ul className="space-y-2 text-sm mb-4">
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Controle total sobre valores e períodos
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Cenários personalizados
                                </li>
                                <li className="flex items-center">
                                    <span className="mr-2">•</span>
                                    Ajustes por categoria específica
                                </li>
                            </ul>
                            <Button variant="outline" asChild>
                                <Link href={route('financial-projections.create')}>
                                    Criar Projeção Manual
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros e Cenários */}
                <Card>
                    <CardHeader>
                        <CardTitle>Cenários e Filtros</CardTitle>
                        <CardDescription>
                            Configure cenários e filtre por categoria
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="font-semibold mb-2">Cenários Disponíveis</h3>
                                <div className="space-y-2">
                                    {available_scenarios.map((scenario) => (
                                        <div key={scenario} className="flex items-center justify-between">
                                            <span className="text-sm">{scenario}</span>
                                            <Badge variant="outline">
                                                {scenario === 'base' ? 'Padrão' :
                                                    scenario === 'optimistic' ? 'Otimista' :
                                                        scenario === 'pessimistic' ? 'Pessimista' : 'Personalizado'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">Filtrar por Categoria</h3>
                                <div className="space-y-2">
                                    {categories.map((category) => (
                                        <div key={category.id} className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <div
                                                    className="w-3 h-3 rounded-full"
                                                    style={{ backgroundColor: category.color || '#6b7280' }}
                                                />
                                                <span className="text-sm">{category.name}</span>
                                            </div>
                                            <Badge variant="outline" className={
                                                category.type === 'income' ? 'bg-green-50' : 'bg-red-50'
                                            }>
                                                {category.type === 'income' ? 'Receita' : 'Despesa'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}