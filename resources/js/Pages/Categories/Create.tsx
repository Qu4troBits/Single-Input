import React, { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

interface CategoryOption {
    id: string;
    name: string;
    type: string;
}

interface Props {
    types: string[];
    categories: CategoryOption[];
}

export default function Create({ types, categories }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        type: '',
        color: '',
        icon: '',
        parent_id: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('categories.store'));
    };

    const getTypeLabel = (type: string) => {
        switch (type) {
            case 'income': return 'Receita';
            case 'expense': return 'Despesa';
            case 'transfer': return 'Transferência';
            default: return type;
        }
    };

    const filteredCategories = categories.filter(cat => cat.type === data.type || !data.type);

    return (
        <Layout>
            <Head title="Nova Categoria" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Nova Categoria</h1>
                    <p className="text-muted-foreground">
                        Cadastre uma nova categoria para organizar suas receitas e despesas
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações da Categoria</CardTitle>
                        <CardDescription>
                            Preencha os dados da categoria
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nome da Categoria *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Ex: Alimentação"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-red-500">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="type">Tipo *</Label>
                                    <Select
                                        value={data.type}
                                        onValueChange={(value) => setData('type', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione o tipo" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {types.map((type) => (
                                                <SelectItem key={type} value={type}>
                                                    {getTypeLabel(type)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.type && (
                                        <p className="text-sm text-red-500">{errors.type}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="color">Cor</Label>
                                    <Input
                                        id="color"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                        placeholder="Ex: #3B82F6"
                                        maxLength={7}
                                    />
                                    {errors.color && (
                                        <p className="text-sm text-red-500">{errors.color}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="icon">Ícone</Label>
                                    <Input
                                        id="icon"
                                        value={data.icon}
                                        onChange={(e) => setData('icon', e.target.value)}
                                        placeholder="Ex: fa-utensils"
                                        maxLength={50}
                                    />
                                    {errors.icon && (
                                        <p className="text-sm text-red-500">{errors.icon}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="parent_id">Categoria Pai</Label>
                                    <Select
                                        value={data.parent_id}
                                        onValueChange={(value) => setData('parent_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione uma categoria pai (opcional)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Nenhuma</SelectItem>
                                            {filteredCategories.map((category) => (
                                                <SelectItem key={category.id} value={category.id}>
                                                    {category.name} ({getTypeLabel(category.type)})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.parent_id && (
                                        <p className="text-sm text-red-500">{errors.parent_id}</p>
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center justify-between pt-4">
                                <Link href={route('categories.index')}>
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Salvando...' : 'Salvar Categoria'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </Layout>
    );
}