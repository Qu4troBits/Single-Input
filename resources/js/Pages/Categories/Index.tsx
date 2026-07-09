import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { CategoryType, CategoryStatus } from '@/types/category';

interface Category {
    id: string;
    name: string;
    type: CategoryType;
    code: string;
    description: string | null;
    color: string | null;
    icon: string | null;
    isOperating: boolean;
    isTaxDeductible: boolean;
    includeInReports: boolean;
    isDefault: boolean;
    parentId: string | null;
    createdAt: string;
    updatedAt: string;
    archivedAt: string | null;
    active: boolean;
}

interface Props extends PageProps {
    categories: Category[];
    meta: {
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    filters: {
        type?: string;
        status?: string;
        is_operating?: boolean;
        is_tax_deductible?: boolean;
        include_in_reports?: boolean;
        is_default?: boolean;
    };
    categoryTypes: Array<{ value: string; label: string }>;
    categoryStatuses: Array<{ value: string; label: string }>;
}

export default function CategoryIndex({
    categories,
    meta,
    filters,
    categoryTypes,
    categoryStatuses
}: Props) {
    const [localFilters, setLocalFilters] = useState({
        type: filters.type || '',
        status: filters.status || '',
        is_operating: filters.is_operating || false,
        is_tax_deductible: filters.is_tax_deductible || false,
        include_in_reports: filters.include_in_reports || false,
        is_default: filters.is_default || false,
    });
    const [isFiltering, setIsFiltering] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (isFiltering) {
                router.get(route('categories.index'), localFilters, {
                    preserveState: true,
                    preserveScroll: true,
                });
                setIsFiltering(false);
            }
        }, 500);

        return () => clearTimeout(timer);
    }, [localFilters, isFiltering]);

    const handleFilterChange = (key: keyof typeof localFilters, value: any) => {
        setLocalFilters(prev => ({ ...prev, [key]: value }));
        setIsFiltering(true);
    };
    
    const getCategoryStatus = (category: Category): CategoryStatus => {
        if (category.archivedAt) {
            return 'archived';
        }
        return category.active ? 'active' : 'inactive';
    };

    const getStatusBadgeVariant = (status: CategoryStatus) => {
        switch (status) {
            case 'active':
                return 'success';
            case 'inactive':
                return 'secondary';
            case 'archived':
                return 'destructive';
            default:
                return 'default';
        }
    };

    const getTypeLabel = (type: CategoryType) => {
        return categoryTypes.find(t => t.value === type)?.label || type;
    };

    const getStatusLabel = (status: CategoryStatus) => {
        return categoryStatuses.find(s => s.value === status)?.label || status;
    };

    const findCategoryName = (id: string | null) => {
        if (!id) return '-';
        const category = categories.find(c => c.id === id);
        return category ? category.name : '-';
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    return (
        <AuthenticatedLayout>
            <Head title="Categorias" />

            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
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

                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>
                            Filtre as categorias por tipo, status e outras características
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="type">Tipo</Label>
                                <Select
                                    value={localFilters.type}
                                    onValueChange={(value) => handleFilterChange('type', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os tipos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Todos os tipos</SelectItem>
                                        {categoryTypes.map((type) => (
                                            <SelectItem key={type.value} value={type.value}>
                                                {type.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select
                                    value={localFilters.status}
                                    onValueChange={(value) => handleFilterChange('status', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Todos os status</SelectItem>
                                        {categoryStatuses.map((status) => (
                                            <SelectItem key={status.value} value={status.value}>
                                                {status.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="is_operating">Operacional</Label>
                                <Select
                                    value={localFilters.is_operating ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('is_operating', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas Operacionais</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="is_tax_deductible">Dedutível</Label>
                                <Select
                                    value={localFilters.is_tax_deductible ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('is_tax_deductible', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas Dedutíveis</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="include_in_reports">Relatórios</Label>
                                <Select
                                    value={localFilters.include_in_reports ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('include_in_reports', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas em Relatórios</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="is_default">Padrão</Label>
                                <Select
                                    value={localFilters.is_default ? 'true' : 'false'}
                                    onValueChange={(value) => handleFilterChange('is_default', value === 'true')}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="false">Todos</SelectItem>
                                        <SelectItem value="true">Apenas Padrão</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Categorias</CardTitle>
                        <CardDescription>
                            Total de {meta.total} categorias
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Código</TableHead>
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
                                {categories.map((category) => {
                                    const status = getCategoryStatus(category);
                                    return (
                                        <TableRow key={category.id}>
                                            <TableCell className="font-medium">
                                                {category.code}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    {category.icon && (
                                                        <span className="text-lg">{category.icon}</span>
                                                    )}
                                                    {category.name}
                                                    {category.isDefault && (
                                                        <Badge variant="outline" className="ml-2">
                                                            Padrão
                                                        </Badge>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {getTypeLabel(category.type)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusBadgeVariant(status)}>
                                                    {getStatusLabel(status)}
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
                                                {findCategoryName(category.parentId)}
                                            </TableCell>
                                            <TableCell>
                                                {formatDate(category.createdAt)}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end space-x-2">
                                                    <Link href={route('categories.show', { category: category.id })}>
                                                        <Button variant="ghost" size="sm">
                                                            Ver
                                                        </Button>
                                                    </Link>
                                                    <Link href={route('categories.edit', { category: category.id })}>
                                                        <Button variant="ghost" size="sm">
                                                            Editar
                                                        </Button>
                                                    </Link>
                                                    {status === 'active' && (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (confirm('Tem certeza que deseja arquivar esta categoria?')) {
                                                                    router.post(route('categories.archive', { category: category.id }));
                                                                }
                                                            }}
                                                        >
                                                            Arquivar
                                                        </Button>
                                                    )}
                                                     {status === 'archived' && (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (confirm('Tem certeza que deseja restaurar esta categoria?')) {
                                                                    router.post(route('categories.restore', { category: category.id }));
                                                                }
                                                            }}
                                                        >
                                                            Restaurar
                                                        </Button>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>

                        {meta.total > 0 && (
                            <div className="flex items-center justify-between mt-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {meta.from} a {meta.to} de {meta.total} categorias
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleFilterChange('type', localFilters.type)}
                                        disabled={meta.current_page === 1}
                                    >
                                        Anterior
                                    </Button>
                                    <span className="text-sm">
                                        Página {meta.current_page} de {meta.last_page}
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleFilterChange('type', localFilters.type)}
                                        disabled={meta.current_page === meta.last_page}
                                    >
                                        Próxima
                                    </Button>
                                </div>
                            </div>
                        )}

                        {categories.length === 0 && (
                            <div className="text-center py-12">
                                <div className="text-muted-foreground mb-4">
                                    Nenhuma categoria encontrada
                                </div>
                                <Link href={route('categories.create')}>
                                    <Button>
                                        Criar Primeira Categoria
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}