import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/Components/ui/form';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { BankAccountType } from '@/Types/BankAccount';

interface Props extends PageProps {
    bankAccountTypes: Array<{ value: string; label: string }>;
}

const formSchema = z.object({
    name: z.string().min(1, 'O nome é obrigatório').max(255, 'O nome deve ter no máximo 255 caracteres'),
    type: z.string().min(1, 'O tipo é obrigatório'),
    bank_code: z.string().min(1, 'O código do banco é obrigatório').max(10, 'O código deve ter no máximo 10 caracteres'),
    bank_name: z.string().min(1, 'O nome do banco é obrigatório').max(255, 'O nome deve ter no máximo 255 caracteres'),
    agency_number: z.string().min(1, 'O número da agência é obrigatório').max(20, 'O número deve ter no máximo 20 caracteres'),
    account_number: z.string().min(1, 'O número da conta é obrigatório').max(30, 'O número deve ter no máximo 30 caracteres'),
    account_digit: z.string().max(2, 'O dígito deve ter no máximo 2 caracteres').optional(),
    initial_balance: z.string().refine((val) => !isNaN(parseFloat(val)), {
        message: 'O saldo inicial deve ser um número válido',
    }),
    description: z.string().max(1000, 'A descrição deve ter no máximo 1000 caracteres').optional(),
    color: z.string().regex(/^#[0-9A-F]{6}$/i, 'A cor deve estar no formato hexadecimal (#RRGGBB)').optional(),
    icon: z.string().max(50, 'O ícone deve ter no máximo 50 caracteres').optional(),
    include_in_dashboard: z.boolean().default(true),
    include_in_reports: z.boolean().default(true),
    is_default: z.boolean().default(false),
});

type FormData = z.infer<typeof formSchema>;

export default function BankAccountCreate({ bankAccountTypes }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);

    const form = useForm<FormData>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: '',
            type: '',
            bank_code: '',
            bank_name: '',
            agency_number: '',
            account_number: '',
            account_digit: '',
            initial_balance: '0.00',
            description: '',
            color: '#3B82F6',
            icon: '🏦',
            include_in_dashboard: true,
            include_in_reports: true,
            is_default: false,
        },
    });

    const onSubmit = async (data: FormData) => {
        setIsSubmitting(true);
        
        try {
            await router.post(route('bank-accounts.store'), data, {
                onSuccess: () => {
                    form.reset();
                },
                onError: (errors) => {
                    Object.keys(errors).forEach((key) => {
                        form.setError(key as keyof FormData, {
                            type: 'manual',
                            message: errors[key],
                        });
                    });
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            });
        } catch (error) {
            setIsSubmitting(false);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Nova Conta Bancária" />

            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Nova Conta Bancária</h1>
                        <p className="text-muted-foreground">
                            Adicione uma nova conta bancária ao sistema
                        </p>
                    </div>
                    <Link href={route('bank-accounts.index')}>
                        <Button variant="outline">
                            Voltar
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações da Conta</CardTitle>
                        <CardDescription>
                            Preencha os dados da conta bancária
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <FormField
                                        control={form.control}
                                        name="name"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Nome da Conta *</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: Conta Corrente Principal" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Nome para identificar a conta no sistema
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="type"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Tipo da Conta *</FormLabel>
                                                <Select onValueChange={field.onChange} defaultValue={field.value}>
                                                    <FormControl>
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Selecione o tipo" />
                                                        </SelectTrigger>
                                                    </FormControl>
                                                    <SelectContent>
                                                        {bankAccountTypes.map((type) => (
                                                            <SelectItem key={type.value} value={type.value}>
                                                                {type.label}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <FormDescription>
                                                    Tipo da conta bancária
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <FormField
                                        control={form.control}
                                        name="bank_code"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Código do Banco *</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: 001" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Código do banco (ex: 001 para Banco do Brasil)
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="bank_name"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Nome do Banco *</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: Banco do Brasil" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Nome completo do banco
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <FormField
                                        control={form.control}
                                        name="agency_number"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Número da Agência *</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: 1234" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Número da agência bancária
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="account_number"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Número da Conta *</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: 987654" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Número da conta bancária
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="account_digit"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Dígito da Conta</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: 1" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Dígito verificador da conta (opcional)
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />
                                </div>

                                <FormField
                                    control={form.control}
                                    name="initial_balance"
                                    render={({ field }) => (
                                        <FormItem>
                                            <FormLabel>Saldo Inicial *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Ex: 1000.00" {...field} />
                                            </FormControl>
                                            <FormDescription>
                                                Saldo inicial da conta bancária
                                            </FormDescription>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />

                                <FormField
                                    control={form.control}
                                    name="description"
                                    render={({ field }) => (
                                        <FormItem>
                                            <FormLabel>Descrição</FormLabel>
                                            <FormControl>
                                                <Textarea 
                                                    placeholder="Ex: Conta utilizada para recebimento de salário e pagamento de contas fixas" 
                                                    {...field} 
                                                />
                                            </FormControl>
                                            <FormDescription>
                                                Descrição detalhada da conta (opcional)
                                            </FormDescription>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <FormField
                                        control={form.control}
                                        name="color"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Cor</FormLabel>
                                                <FormControl>
                                                    <div className="flex items-center gap-2">
                                                        <Input 
                                                            type="color" 
                                                            className="w-12 h-12 p-1" 
                                                            {...field} 
                                                        />
                                                        <Input 
                                                            placeholder="#3B82F6" 
                                                            {...field} 
                                                        />
                                                    </div>
                                                </FormControl>
                                                <FormDescription>
                                                    Cor para identificar a conta (opcional)
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="icon"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Ícone</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Ex: 🏦" {...field} />
                                                </FormControl>
                                                <FormDescription>
                                                    Ícone para identificar a conta (opcional)
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <FormField
                                        control={form.control}
                                        name="include_in_dashboard"
                                        render={({ field }) => (
                                            <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <FormLabel className="text-base">
                                                        Incluir no Dashboard
                                                    </FormLabel>
                                                    <FormDescription>
                                                        Mostrar esta conta no dashboard principal
                                                    </FormDescription>
                                                </div>
                                                <FormControl>
                                                    <input
                                                        type="checkbox"
                                                        className="h-4 w-4"
                                                        checked={field.value}
                                                        onChange={field.onChange}
                                                    />
                                                </FormControl>
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="include_in_reports"
                                        render={({ field }) => (
                                            <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <FormLabel className="text-base">
                                                        Incluir em Relatórios
                                                    </FormLabel>
                                                    <FormDescription>
                                                        Incluir esta conta nos relatórios financeiros
                                                    </FormDescription>
                                                </div>
                                                <FormControl>
                                                    <input
                                                        type="checkbox"
                                                        className="h-4 w-4"
                                                        checked={field.value}
                                                        onChange={field.onChange}
                                                    />
                                                </FormControl>
                                            </FormItem>
                                        )}
                                    />

                                    <FormField
                                        control={form.control}
                                        name="is_default"
                                        render={({ field }) => (
                                            <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <FormLabel className="text-base">
                                                        Conta Padrão
                                                    </FormLabel>
                                                    <FormDescription>
                                                        Definir como conta padrão do sistema
                                                    </FormDescription>
                                                </div>
                                                <FormControl>
                                                    <input
                                                        type="checkbox"
                                                        className="h-4 w-4"
                                                        checked={field.value}
                                                        onChange={field.onChange}
                                                    />
                                                </FormControl>
                                            </FormItem>
                                        )}
                                    />
                                </div>

                                <div className="flex items-center justify-end gap-4">
                                    <Link href={route('bank-accounts.index')}>
                                        <Button type="button" variant="outline">
                                            Cancelar
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={isSubmitting}>
                                        {isSubmitting ? 'Salvando...' : 'Salvar Conta'}
                                    </Button>
                                </div>
                            </form>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
