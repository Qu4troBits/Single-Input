import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, Plus, Trash2 } from 'lucide-react';
import { PageProps } from '@/types';
import { useState } from 'react';

interface CreateProps extends PageProps {
    categories: Array<{
        id: string;
        name: string;
        type: 'revenue' | 'expense';
    }>;
}

interface ProjectionItemForm {
    id: string;
    date: string;
    description: string;
    amount: string;
    category_id: string;
    notes: string;
}

export default function Create({ auth, categories }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        type: 'revenue' as 'revenue' | 'expense' | 'profit' | 'cash_flow' | 'balance_sheet',
        period_type: 'monthly' as 'monthly' | 'quarterly' | 'yearly',
        year_month: new Date().toISOString().slice(0, 7),
        year: new Date().getFullYear().toString(),
        quarter: 1,
        scenario: 'base' as 'base' | 'optimistic' | 'pessimistic' | 'custom',
        title: '',
        notes: '',
        items: [] as ProjectionItemForm[],
    });

    const [items, setItems] = useState<ProjectionItemForm[]>([
        {
            id: '1',
            date: new Date().toISOString().slice(0, 10),
            description: '',
            amount: '',
            category_id: '',
            notes: '',
        },
    ]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('financial-projections.store'), {
            data: {
                ...data,
                items: items.map(item => ({
                    ...item,
                    amount: parseFloat(item.amount) || 0,
                })),
            },
        });
    };

    const addItem = () => {
        const newItem: ProjectionItemForm = {
            id: (items.length + 1).toString(),
            date: new Date().toISOString().slice(0, 10),
            description: '',
            amount: '',
            category_id: '',
            notes: '',
        };
        setItems([...items, newItem]);
    };

    const removeItem = (id: string) => {
        if (items.length > 1) {
            setItems(items.filter(item => item.id !== id));
        }
    };

    const updateItem = (id: string, field: keyof ProjectionItemForm, value: string) => {
        setItems(items.map(item => 
            item.id === id ? { ...item, [field]: value } : item
        ));
    };

    const revenueCategories = categories.filter(c => c.type === 'revenue');
    const expenseCategories = categories.filter(c => c.type === 'expense');

    const getAvailableCategories = () => {
        if (data.type === 'revenue') return revenueCategories;
        if (data.type === 'expense') return expenseCategories;
        return categories;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="icon" asChild>
                        <Link href={route('financial-projections.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Nova Projeção Financeira</h2>
                        <p className="text-muted-foreground">
                            Crie uma projeção financeira para planejamento
                        </p>
                    </div>
                </div>
            }
        >
            <Head title="Nova Projeção Financeira" />

            <div className="max-w-4xl mx-auto">
                <form onSubmit={handleSubmit}>
                    <div className="grid gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Configuração da Projeção</CardTitle>
                                <CardDescription>
                                    Defina os parâmetros básicos da projeção
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="type">Tipo de Projeção</Label>
                                        <Select
                                            value={data.type}
                                            onValueChange={(value: any) => setData('type', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione o tipo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="revenue">Receita</SelectItem>
                                                <SelectItem value="expense">Despesa</SelectItem>
                                                <SelectItem value="profit">Lucro</SelectItem>
                                                <SelectItem value="cash_flow">Fluxo de Caixa</SelectItem>
                                                <SelectItem value="balance_sheet">Balanço Patrimonial</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.type && (
                                            <p className="text-sm text-destructive">{errors.type}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="period_type">Período</Label>
                                        <Select
                                            value={data.period_type}
                                            onValueChange={(value: any) => setData('period_type', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione o período" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="monthly">Mensal</SelectItem>
                                                <SelectItem value="quarterly">Trimestral</SelectItem>
                                                <SelectItem value="yearly">Anual</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.period_type && (
                                            <p className="text-sm text-destructive">{errors.period_type}</p>
                                        )}
                                    </div>
                                </div>

                                {data.period_type === 'monthly' && (
                                    <div className="space-y-2">
                                        <Label htmlFor="year_month">Mês/Ano</Label>
                                        <Input
                                            id="year_month"
                                            type="month"
                                            value={data.year_month}
                                            onChange={(e) => setData('year_month', e.target.value)}
                                        />
                                        {errors.year_month && (
                                            <p className="text-sm text-destructive">{errors.year_month}</p>
                                        )}
                                    </div>
                                )}

                                {data.period_type === 'quarterly' && (
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="year">Ano</Label>
                                            <Input
                                                id="year"
                                                type="number"
                                                min="2000"
                                                max="2100"
                                                value={data.year}
                                                onChange={(e) => setData('year', e.target.value)}
                                            />
                                            {errors.year && (
                                                <p className="text-sm text-destructive">{errors.year}</p>
                                            )}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="quarter">Trimestre</Label>
                                            <Select
                                                value={data.quarter.toString()}
                                                onValueChange={(value) => setData('quarter', parseInt(value))}
                                            >
                                                <SelectTrigger>
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
                                                <p className="text-sm text-destructive">{errors.quarter}</p>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {data.period_type === 'yearly' && (
                                    <div className="space-y-2">
                                        <Label htmlFor="year">Ano</Label>
                                        <Input
                                            id="year"
                                            type="number"
                                            min="2000"
                                            max="2100"
                                            value={data.year}
                                            onChange={(e) => setData('year', e.target.value)}
                                        />
                                        {errors.year && (
                                            <p className="text-sm text-destructive">{errors.year}</p>
                                        )}
                                    </div>
                                )}

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
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Itens da Projeção</CardTitle>
                                        <CardDescription>
                                            Adicione os itens que compõem esta projeção
                                        </CardDescription>
                                    </div>
                                    <Button type="button" onClick={addItem} size="sm">
                                        <Plus className="h-4 w-4 mr-2" />
                                        Adicionar Item
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {items.map((item, index) => (
                                        <div key={item.id} className="border rounded-lg p-4 space-y-4">
                                            <div className="flex items-center justify-between">
                                                <h4 className="font-medium">Item {index + 1}</h4>
                                                {items.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => removeItem(item.id)}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div className="space-y-2">
                                                    <Label htmlFor={`date-${item.id}`}>Data</Label>
                                                    <Input
                                                        id={`date-${item.id}`}
                                                        type="date"
                                                        value={item.date}
                                                        onChange={(e) => updateItem(item.id, 'date', e.target.value)}
                                                    />
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor={`amount-${item.id}`}>Valor (R$)</Label>
                                                    <Input
                                                        id={`amount-${item.id}`}
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        value={item.amount}
                                                        onChange={(e) => updateItem(item.id, 'amount', e.target.value)}
                                                        placeholder="0,00"
                                                    />
                                                </div>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor={`description-${item.id}`}>Descrição</Label>
                                                <Input
                                                    id={`description-${item.id}`}
                                                    value={item.description}
                                                    onChange={(e) => updateItem(item.id, 'description', e.target.value)}
                                                    placeholder="Descrição do item"
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor={`category_id-${item.id}`}>Categoria</Label>
                                                <Select
                                                    value={item.category_id}
                                                    onValueChange={(value) => updateItem(item.id, 'category_id', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Selecione uma categoria" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="">Sem categoria</SelectItem>
                                                        {getAvailableCategories().map(category => (
                                                            <SelectItem key={category.id} value={category.id}>
                                                                {category.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor={`notes-${item.id}`}>Observações do Item</Label>
                                                <Textarea
                                                    id={`notes-${item.id}`}
                                                    value={item.notes}
                                                    onChange={(e) => updateItem(item.id, 'notes', e.target.value)}
                                                    placeholder="Observações específicas deste item"
                                                    rows={2}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-2">
                            <Button variant="outline" asChild>
                                <Link href={route('financial-projections.index')}>
                                    Cancelar
                                </Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Salvando...' : 'Salvar Projeção'}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}