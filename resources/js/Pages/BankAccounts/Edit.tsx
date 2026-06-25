import React, { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

interface BankAccount {
    id: string;
    name: string;
    type: string;
    status: string;
    bank_code: string | null;
    agency: string | null;
    account_number: string | null;
    account_digit: string | null;
    description: string | null;
}

interface Props {
    bankAccount: BankAccount;
    types: string[];
    statuses: string[];
}

export default function Edit({ bankAccount, types, statuses }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: bankAccount.name,
        type: bankAccount.type,
        status: bankAccount.status,
        bank_code: bankAccount.bank_code || '',
        agency: bankAccount.agency || '',
        account_number: bankAccount.account_number || '',
        account_digit: bankAccount.account_digit || '',
        description: bankAccount.description || '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('bank-accounts.update', bankAccount.id));
    };

    const getTypeLabel = (type: string) => {
        switch (type) {
            case 'checking': return 'Conta Corrente';
            case 'savings': return 'Poupança';
            case 'investment': return 'Investimento';
            case 'credit_card': return 'Cartão de Crédito';
            case 'wallet': return 'Carteira';
            case 'other': return 'Outro';
            default: return type;
        }
    };

    const getStatusLabel = (status: string) => {
        switch (status) {
            case 'active': return 'Ativo';
            case 'inactive': return 'Inativo';
            case 'closed': return 'Fechado';
            default: return status;
        }
    };

    return (
        <Layout>
            <Head title="Editar Conta Bancária" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Editar Conta Bancária</h1>
                    <p className="text-muted-foreground">
                        Atualize os dados da conta bancária
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações da Conta</CardTitle>
                        <CardDescription>
                            Atualize os dados da conta bancária
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nome da Conta *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Ex: Conta Corrente Banco X"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-red-500">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="type">Tipo de Conta *</Label>
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
                                    <Label htmlFor="status">Status *</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(value) => setData('status', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione o status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {statuses.map((status) => (
                                                <SelectItem key={status} value={status}>
                                                    {getStatusLabel(status)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.status && (
                                        <p className="text-sm text-red-500">{errors.status}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="bank_code">Código do Banco</Label>
                                    <Input
                                        id="bank_code"
                                        value={data.bank_code}
                                        onChange={(e) => setData('bank_code', e.target.value)}
                                        placeholder="Ex: 001"
                                        maxLength={10}
                                    />
                                    {errors.bank_code && (
                                        <p className="text-sm text-red-500">{errors.bank_code}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="agency">Agência</Label>
                                    <Input
                                        id="agency"
                                        value={data.agency}
                                        onChange={(e) => setData('agency', e.target.value)}
                                        placeholder="Ex: 1234"
                                        maxLength={20}
                                    />
                                    {errors.agency && (
                                        <p className="text-sm text-red-500">{errors.agency}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="account_number">Número da Conta</Label>
                                    <Input
                                        id="account_number"
                                        value={data.account_number}
                                        onChange={(e) => setData('account_number', e.target.value)}
                                        placeholder="Ex: 123456"
                                        maxLength={30}
                                    />
                                    {errors.account_number && (
                                        <p className="text-sm text-red-500">{errors.account_number}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="account_digit">Dígito</Label>
                                    <Input
                                        id="account_digit"
                                        value={data.account_digit}
                                        onChange={(e) => setData('account_digit', e.target.value)}
                                        placeholder="Ex: 1"
                                        maxLength={2}
                                    />
                                    {errors.account_digit && (
                                        <p className="text-sm text-red-500">{errors.account_digit}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Descrição</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Descrição opcional da conta"
                                    rows={3}
                                />
                                {errors.description && (
                                    <p className="text-sm text-red-500">{errors.description}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-between pt-4">
                                <Link href={route('bank-accounts.index')}>
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Atualizando...' : 'Atualizar Conta'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </Layout>
    );
}