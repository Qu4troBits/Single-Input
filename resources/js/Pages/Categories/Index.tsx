import React from 'react';
import { Head, Link } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

interface Category {
    id: string;
    name: string;
    type: string;
    status: string;
    color: string | null;
    icon: string | null;
    parent_id: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    categories: Category[];
}

export default function Index({ categories }: Props) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'inactive': return 'bg-yellow-100 text-yellow-800';
            case 'archived': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getTypeLabel = (type: string) => {
        switch (type) {
            case 'income': return 'Receita';
            case 'expense': return 'Despesa';
            case 'transfer': return 'Transferência';
            default: return type;
        }
    };

    const getStatusLabel = (status: string) => {
        switch (status) {
            case 'active': return 'Ativo';
            case 'inactive': return 'Inativo';
            case 'archived': return 'Arquivado';
            default: return status;
        }
    };

    const findCategoryName = (id: string | null) => {
        if (!id) return '-';
        const category = categories.find(c => c.id === id);
        return category ? category.name : '-';
    };

    return (
        <Layout>
            <Head title="Categorias" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Categorias</h1>
                        <p className="text-muted-foreground">
                            Gerencie suas categorias de receitas e despesas
                        </p>
                    </div>
                    <Link href={route('categories.create')}>
                        <Button>
                            Nova Categoria
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Categorias</CardTitle>
                        <CardDescription>
                            Lista de todas as categorias cadastradas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {categories.length === 0 ? (
                            <div className="text-center py-12">
                                <p className="text-muted-foreground">
                                    Nenhuma categoria cadastrada ainda.
                                </p>
                                <Link href={route('categories.create')} className="mt-4 inline-block">
                                    <Button variant="outline">
                                        Criar Primeira Categoria
                                    </Button>
                                </Link>
                            </div>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nome</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Cor</TableHead>
                                        <TableHead>Ícone</TableHead>
                                        <TableHead>Categoria Pai</TableHead>
                                        <TableHead>Criado em</TableHead>
                                        <TableHead className="text-right">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {categories.map((category) => (
                                        <TableRow key={category.id}>
                                            <TableCell className="font-medium">
                                                {category.name}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {getTypeLabel(category.type)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge className={getStatusColor(category.status)}>
                                                    {getStatusLabel(category.status)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {category.color && (
                                                    <div className="flex items-center space-x-2">
                                                        <div
                                                            className="w-4 h-4 rounded-full border"
                                                            style={{ backgroundColor: category.color }}
                                                        />
                                                        <span className="text-sm">{category.color}</span>
                                                    </div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {category.icon && (
                                                    <span className="text-sm">{category.icon}</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {findCategoryName(category.parent_id)}
                                            </TableCell>
                                            <TableCell>
                                                {new Date(category.created_at).toLocaleDateString('pt-BR')}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end space-x-2">
                                                    <Link href={route('categories.edit', category.id)}>
                                                        <Button variant="outline" size="sm">
                                                            Editar
                                                        </Button>
                                                    </Link>
                                                    <Link
                                                        href={route('categories.destroy', category.id)}
                                                        method="delete"
                                                        as="button"
                                                        className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
                                                        onClick={(e) => {
                                                            if (!confirm('Tem certeza que deseja excluir esta categoria?')) {
                                                                e.preventDefault();
                                                            }
                                                        }}
                                                    >
                                                        Excluir
                                                    </Link>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </Layout>
    );
}