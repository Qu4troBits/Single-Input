import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/Components/ui/form';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { BankAccountType, BankAccountStatus } from '@/types/bank-account';

interface BankAccount {
    id: string;
    name: string;
    type: BankAccountType;
    bankCode: string;
    bankName: string;
    agencyNumber: string;
    accountNumber: string;
    accountDigit: string | null;
    initialBalance: string;
    currentBalance: string;
    status: BankAccountStatus;
    description: string | null;
    color: string | null;
    icon: string | null;
    includeInDashboard: boolean;
    includeInReports: boolean;
    isDefault: boolean;
    createdAt: string;
    updatedAt: string;
}

interface Props extends PageProps {
    bankAccount: BankAccount;
    bankAccountTypes: Array<{ value: string; label: string }>;
    bankAccountStatuses: Array<{ value: string; label: string }>;
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
    status: z.string().min(1, 'O status é obrigatório'),
    description: z.string().max(1000, 'A descrição deve ter no máximo 1000 caracteres').optional(),
    color: z.string().regex(/^#[0-9A-F]{6}$/i, 'A cor deve estar no formato hexadecimal (#RRGGBB)').optional(),
    icon: z.string().max(50, 'O ícone deve ter no máximo 50 caracteres').optional(),
    include_in_dashboard: z.boolean(),
    include_in_reports: z.boolean(),
    is_default: z.boolean(),
});

type FormData = z.infer<typeof formSchema>;

export default function BankAccountEdit({ bankAccount, bankAccountTypes, bankAccountStatuses }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);

    const form = useForm<FormData>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: bankAccount.name,
            type: bankAccount.type,
            bank_code: bankAccount.bankCode,
            bank_name: bankAccount.bankName,
            agency_number: bankAccount.agencyNumber,
            account_number: bankAccount.accountNumber,
            account_digit: bankAccount.accountDigit || '',
            initial_balance: bankAccount.initialBalance,
            status: bankAccount.status,
            description: bankAccount.description || '',
            color: bankAccount.color || '#3B82F6',
            icon: bankAccount.icon || '🏦',
            include_in_dashboard: bankAccount.includeInDashboard,
            include_in_reports: bankAccount.includeInReports,
            is_default: bankAccount.isDefault,
        },
    });

    const onSubmit = async (data: FormData) => {
        setIsSubmitting(true);
        
        try {
            await router.put(route('bank-accounts.update', { bank_account: bankAccount.id }), data, {
                onSuccess: () => {
                    // Redirecionamento será feito pelo controller
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

    const getStatusLabel = (status: BankAccountStatus) => {
        return bankAccountStatuses.find(s => s.value === status)?.label || status;
    };

    const getTypeLabel = (type: BankAccountType) => {
        return bankAccountTypes.find(t => t.value === type)?.label || type;
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Editar Conta - ${bankAccount.name}`} />

            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Editar Conta Bancária
                        </h1>
                        <p className="text-muted-foreground">
                            Atualize os dados da conta "{bankAccount.name}"
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={route('bank-accounts.index')}>
                            <Button variant="outline">
                                Voltar
                            </Button>
                        </Link>
                        <Link href={route('bank-accounts.show', { bank_account: bankAccount.id })}>
                            <Button variant="outline">
                                Ver Detalhes
                            </Button>
                        </Link>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações da Conta</CardTitle>
                        <CardDescription>
                            Atualize os dados da conta bancária
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

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        name="status"
                                        render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Status *</FormLabel>
                                                <Select onValueChange={field.onChange} defaultValue={field.value}>
                                                    <FormControl>
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Selecione o status" />
                                                        </SelectTrigger>
                                                    </FormControl>
                                                    <SelectContent>
                                                        {bankAccountStatuses.map((status) => (
                                                            <SelectItem key={status.value} value={status.value}>
                                                                {status.label}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <FormDescription>
                                                    Status atual da conta
                                                </FormDescription>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />
                                </div>

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
                                        {isSubmitting ? 'Atualizando...' : 'Atualizar Conta'}
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
