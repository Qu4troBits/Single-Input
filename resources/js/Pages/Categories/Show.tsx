import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/Components/ui/alert-dialog';
import { formatDate } from '@/Utils/date';
import { getCategoryTypeLabel, getCategoryStatusLabel, getCategoryStatusColor } from '@/Utils/category';

interface Category {
    id: string;
    name: string;
    type: string;
    code: string;
    status: string;
    description: string | null;
    color: string | null;
    icon: string | null;
    isOperating: boolean;
    isTaxDeductible: boolean;
    includeInReports: boolean;
    isDefault: boolean;
    parentId: string | null;
    parentName: string | null;
    createdAt: string;
    updatedAt: string;
}

interface Props {
    category: Category;
}

export default function CategoryShow({ category }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

    const handleDelete = async () => {
        setIsDeleting(true);
        
        try {
            await router.delete(route('categories.destroy', category.id));
        } catch (error) {
            setIsDeleting(false);
            setShowDeleteConfirm(false);
        }
    };

    const handleArchive = async () => {
        try {
            await router.post(route('categories.archive', category.id));
        } catch (error) {
            console.error('Erro ao arquivar categoria:', error);
        }
    };

    const handleRestore = async () => {
        try {
            await router.post(route('categories.restore', category.id));
        } catch (error) {
            console.error('Erro ao restaurar categoria:', error);
        }
    };

    return (
        <Layout>
            <Head title={`Categoria: ${category.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Categoria: {category.name}</h1>
                        <p className="text-muted-foreground mt-2">
                            Detalhes da categoria {category.code}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('categories.index')}>
                                Voltar
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={route('categories.edit', category.id)}>
                                Editar
                            </Link>
                        </Button>
                        {category.status === 'active' && (
                            <Button variant="outline" onClick={handleArchive}>
                                Arquivar
                            </Button>
                        )}
                        {category.status === 'archived' && (
                            <Button variant="outline" onClick={handleRestore}>
                                Restaurar
                            </Button>
                        )}
                        <AlertDialog open={showDeleteConfirm} onOpenChange={setShowDeleteConfirm}>
                            <AlertDialogTrigger asChild>
                                <Button variant="destructive" disabled={isDeleting}>
                                    {isDeleting ? 'Excluindo...' : 'Excluir'}
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Confirmar exclusão</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Tem certeza que deseja excluir a categoria "{category.name}"?
                                        Esta ação não pode ser desfeita.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                    <AlertDialogAction onClick={handleDelete} disabled={isDeleting}>
                                        {isDeleting ? 'Excluindo...' : 'Excluir'}
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Informações da Categoria</CardTitle>
                                <CardDescription>
                                    Detalhes básicos da categoria
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Nome</p>
                                        <p className="text-lg font-semibold">{category.name}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Código</p>
                                        <p className="text-lg font-semibold">{category.code}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Tipo</p>
                                        <Badge variant="outline" className="mt-1">
                                            {getCategoryTypeLabel(category.type)}
                                        </Badge>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Status</p>
                                        <Badge className={`mt-1 ${getCategoryStatusColor(category.status)}`}>
                                            {getCategoryStatusLabel(category.status)}
                                        </Badge>
                                    </div>
                                </div>

                                <Separator />

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Descrição</p>
                                    <p className="mt-1 text-sm">
                                        {category.description || 'Nenhuma descrição fornecida.'}
                                    </p>
                                </div>

                                <Separator />

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Categoria Pai</p>
                                        <p className="mt-1 text-sm">
                                            {category.parentName || 'Nenhuma (categoria raiz)'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Cor</p>
                                        <div className="flex items-center gap-2 mt-1">
                                            {category.color && (
                                                <div 
                                                    className="w-6 h-6 rounded-full border"
                                                    style={{ backgroundColor: category.color }}
                                                />
                                            )}
                                            <span className="text-sm">{category.color || 'Nenhuma'}</span>
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Ícone</p>
                                        <p className="mt-1 text-sm">
                                            {category.icon || 'Nenhum'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">É operacional?</p>
                                        <Badge variant="outline" className="mt-1">
                                            {category.isOperating ? 'Sim' : 'Não'}
                                        </Badge>
                                    </div>
                                </div>

                                <Separator />

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">É dedutível?</p>
                                        <Badge variant="outline" className="mt-1">
                                            {category.isTaxDeductible ? 'Sim' : 'Não'}
                                        </Badge>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Incluir em relatórios?</p>
                                        <Badge variant="outline" className="mt-1">
                                            {category.includeInReports ? 'Sim' : 'Não'}
                                        </Badge>
                                    </div>
                                </div>

                                <Separator />

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">É padrão?</p>
                                    <Badge variant="outline" className="mt-1">
                                        {category.isDefault ? 'Sim' : 'Não'}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Informações do Sistema</CardTitle>
                                <CardDescription>
                                    Metadados da categoria
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Criado em</p>
                                    <p className="text-sm">{formatDate(category.createdAt)}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Atualizado em</p>
                                    <p className="text-sm">{formatDate(category.updatedAt)}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">ID</p>
                                    <p className="text-sm font-mono text-xs">{category.id}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Ações Rápidas</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Button variant="outline" className="w-full justify-start" asChild>
                                    <Link href={route('transactions.index', { category_id: category.id })}>
                                        Ver transações
                                    </Link>
                                </Button>
                                <Button variant="outline" className="w-full justify-start" asChild>
                                    <Link href={route('categories.create', { parent_id: category.id })}>
                                        Criar subcategoria
                                    </Link>
                                </Button>
                                {category.parentId && (
                                    <Button variant="outline" className="w-full justify-start" asChild>
                                        <Link href={route('categories.show', category.parentId)}>
                                            Ver categoria pai
                                        </Link>
                                    </Button>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Layout>
    );
}