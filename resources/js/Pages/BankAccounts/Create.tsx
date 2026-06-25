import React, { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

interface Props {
    types: string[];
}

export default function Create({ types }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        type: '',
        bank_code: '',
        agency: '',
        account_number: '',
        account_digit: '',
        description: '',
        initial_balance: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('bank-accounts.store'));
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

    return (
        <Layout>
            <Head title="Nova Conta Bancária" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Nova Conta Bancária</h1>
                    <p className="text-muted-foreground">
                        Cadastre uma nova conta bancária ou carteira
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações da Conta</CardTitle>
                        <CardDescription>
                            Preencha os dados da conta bancária
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

                                <div className="space-y-2">
                                    <Label htmlFor="initial_balance">Saldo Inicial *</Label>
                                    <Input
                                        id="initial_balance"
                                        type="number"
                                        step="0.01"
                                        value={data.initial_balance}
                                        onChange={(e) => setData('initial_balance', e.target.value)}
                                        placeholder="Ex: 1000.00"
                                        required
                                    />
                                    {errors.initial_balance && (
                                        <p className="text-sm text-red-500">{errors.initial_balance}</p>
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
                                    {processing ? 'Salvando...' : 'Salvar Conta'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </Layout>
    );
}