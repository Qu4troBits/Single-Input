import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';
import { formatBRL } from '@/Types/Money';
import { BankAccountType, BankAccountStatus } from '@/Types/BankAccount';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/Components/ui/alert-dialog';

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
}

export default function BankAccountShow({ bankAccount }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

    const handleDelete = async () => {
        setIsDeleting(true);
        
        try {
            await router.delete(route('bank-accounts.destroy', bankAccount.id));
        } catch (error) {
            setIsDeleting(false);
            setShowDeleteConfirm(false);
        }
    };

    const getStatusBadgeVariant = (status: BankAccountStatus) => {
        switch (status) {
            case BankAccountStatus.ACTIVE:
                return 'success';
            case BankAccountStatus.INACTIVE:
                return 'secondary';
            case BankAccountStatus.CLOSED:
                return 'destructive';
            case BankAccountStatus.BLOCKED:
                return 'warning';
            default:
                return 'default';
        }
    };

    const getStatusLabel = (status: BankAccountStatus) => {
        switch (status) {
            case BankAccountStatus.ACTIVE:
                return 'Ativa';
            case BankAccountStatus.INACTIVE:
                return 'Inativa';
            case BankAccountStatus.CLOSED:
                return 'Encerrada';
            case BankAccountStatus.BLOCKED:
                return 'Bloqueada';
            default:
                return status;
        }
    };

    const getTypeLabel = (type: BankAccountType) => {
        switch (type) {
            case BankAccountType.CHECKING:
                return 'Conta Corrente';
            case BankAccountType.SAVINGS:
                return 'Conta Poupança';
            case BankAccountType.INVESTMENT:
                return 'Conta Investimento';
            case BankAccountType.CREDIT_CARD:
                return 'Cartão de Crédito';
            case BankAccountType.WALLET:
                return 'Carteira Digital';
            case BankAccountType.OTHER:
                return 'Outro';
            default:
                return type;
        }
    };

    const getFullAccountNumber = () => {
        return bankAccount.accountNumber + (bankAccount.accountDigit ? '-' + bankAccount.accountDigit : '');
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Conta Bancária - ${bankAccount.name}`} />

            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {bankAccount.name}
                        </h1>
                        <p className="text-muted-foreground">
                            Detalhes da conta bancária
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={route('bank-accounts.index')}>
                            <Button variant="outline">
                                Voltar
                            </Button>
                        </Link>
                        <Link href={route('bank-accounts.edit', bankAccount.id)}>
                            <Button>
                                Editar
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Informações da Conta</CardTitle>
                                <CardDescription>
                                    Detalhes completos da conta bancária
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Banco
                                        </h3>
                                        <p className="text-lg font-medium">
                                            {bankAccount.bankName}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Código: {bankAccount.bankCode}
                                        </p>
                                    </div>

                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Agência/Conta
                                        </h3>
                                        <p className="text-lg font-medium">
                                            Agência: {bankAccount.agencyNumber}
                                        </p>
                                        <p className="text-lg font-medium">
                                            Conta: {getFullAccountNumber()}
                                        </p>
                                    </div>
                                </div>

                                <Separator />

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Tipo
                                        </h3>
                                        <Badge variant="outline" className="text-base">
                                            {getTypeLabel(bankAccount.type)}
                                        </Badge>
                                    </div>

                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Status
                                        </h3>
                                        <Badge variant={getStatusBadgeVariant(bankAccount.status)}>
                                            {getStatusLabel(bankAccount.status)}
                                        </Badge>
                                    </div>
                                </div>

                                <Separator />

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Saldo Inicial
                                        </h3>
                                        <p className={`text-2xl font-bold ${parseFloat(bankAccount.initialBalance) >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                            {formatBRL(bankAccount.initialBalance)}
                                        </p>
                                    </div>

                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Saldo Atual
                                        </h3>
                                        <p className={`text-2xl font-bold ${parseFloat(bankAccount.currentBalance) >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                            {formatBRL(bankAccount.currentBalance)}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Diferença: {formatBRL(
                                                (parseFloat(bankAccount.currentBalance) - parseFloat(bankAccount.initialBalance)).toString()
                                            )}
                                        </p>
                                    </div>
                                </div>

                                {bankAccount.description && (
                                    <>
                                        <Separator />
                                        <div>
                                            <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                                Descrição
                                            </h3>
                                            <p className="text-base whitespace-pre-wrap">
                                                {bankAccount.description}
                                            </p>
                                        </div>
                                    </>
                                )}

                                <Separator />

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            checked={bankAccount.includeInDashboard}
                                            readOnly
                                            className="h-4 w-4"
                                        />
                                        <span className="text-sm">
                                            Incluir no Dashboard
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            checked={bankAccount.includeInReports}
                                            readOnly
                                            className="h-4 w-4"
                                        />
                                        <span className="text-sm">
                                            Incluir em Relatórios
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            checked={bankAccount.isDefault}
                                            readOnly
                                            className="h-4 w-4"
                                        />
                                        <span className="text-sm">
                                            Conta Padrão
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Metadados</CardTitle>
                                <CardDescription>
                                    Informações do sistema
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                        Criado em
                                    </h3>
                                    <p className="text-base">
                                        {formatDate(bankAccount.createdAt)}
                                    </p>
                                </div>

                                <div>
                                    <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                        Atualizado em
                                    </h3>
                                    <p className="text-base">
                                        {formatDate(bankAccount.updatedAt)}
                                    </p>
                                </div>

                                {bankAccount.color && (
                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Cor
                                        </h3>
                                        <div className="flex items-center gap-2">
                                            <div 
                                                className="w-6 h-6 rounded-full border"
                                                style={{ backgroundColor: bankAccount.color }}
                                            />
                                            <span className="text-base">
                                                {bankAccount.color}
                                            </span>
                                        </div>
                                    </div>
                                )}

                                {bankAccount.icon && (
                                    <div>
                                        <h3 className="text-sm font-medium text-muted-foreground mb-1">
                                            Ícone
                                        </h3>
                                        <p className="text-2xl">
                                            {bankAccount.icon}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="mt-6">
                            <CardHeader>
                                <CardTitle>Ações</CardTitle>
                                <CardDescription>
                                    Gerenciar esta conta bancária
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <Link href={route('bank-accounts.edit', bankAccount.id)} className="w-full">
                                    <Button variant="outline" className="w-full">
                                        Editar Conta
                                    </Button>
                                </Link>

                                <AlertDialog open={showDeleteConfirm} onOpenChange={setShowDeleteConfirm}>
                                    <AlertDialogTrigger asChild>
                                        <Button variant="destructive" className="w-full">
                                            Excluir Conta
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>
                                                Confirmar exclusão
                                            </AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Tem certeza que deseja excluir a conta "{bankAccount.name}"?
                                                Esta ação não pode ser desfeita.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>
                                                Cancelar
                                            </AlertDialogCancel>
                                            <AlertDialogAction
                                                onClick={handleDelete}
                                                disabled={isDeleting}
                                                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                            >
                                                {isDeleting ? 'Excluindo...' : 'Excluir'}
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
