import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Link } from '@inertiajs/react';
import { BankAccount } from '@/types';
import { useState } from 'react';

interface BankReconciliationImportProps extends PageProps {
    bank_account: BankAccount;
}

interface BankStatementItem {
    id: string;
    date: string;
    description: string;
    amount: string;
    type: 'credit' | 'debit';
    bank_reference?: string;
    notes?: string;
}

export default function Import({ auth, bank_account }: BankReconciliationImportProps) {
    const [items, setItems] = useState<BankStatementItem[]>([
        {
            id: `item-${Date.now()}-1`,
            date: new Date().toISOString().split('T')[0],
            description: '',
            amount: '',
            type: 'credit',
            bank_reference: '',
            notes: '',
        },
    ]);

    const { data, setData, post, processing, errors } = useForm({
        statement_date: new Date().toISOString().split('T')[0],
        statement_type: 'manual' as 'csv' | 'ofx' | 'pdf' | 'manual',
        original_filename: '',
        notes: '',
        items: items,
    });

    const addItem = () => {
        const newItem: BankStatementItem = {
            id: `item-${Date.now()}-${items.length + 1}`,
            date: new Date().toISOString().split('T')[0],
            description: '',
            amount: '',
            type: 'credit',
            bank_reference: '',
            notes: '',
        };
        setItems([...items, newItem]);
        setData('items', [...items, newItem]);
    };

    const removeItem = (index: number) => {
        const newItems = items.filter((_, i) => i !== index);
        setItems(newItems);
        setData('items', newItems);
    };

    const updateItem = (index: number, field: keyof BankStatementItem, value: string) => {
        const newItems = [...items];
        newItems[index] = { ...newItems[index], [field]: value };
        setItems(newItems);
        setData('items', newItems);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('bank-reconciliation.import', bank_account.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">
                            Importar Extrato Bancário
                        </h2>
                        <p className="text-muted-foreground">
                            Conta: {bank_account.name} • Banco: {bank_account.bank_name}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('bank-reconciliation.show', bank_account.id)}>
                                Voltar
                            </Link>
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title={`Importar Extrato: ${bank_account.name}`} />

            <div className="max-w-4xl mx-auto">
                <Card>
                    <CardHeader>
                        <CardTitle>Importar Extrato</CardTitle>
                        <CardDescription>
                            Adicione os itens do extrato bancário para conciliação
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Informações do Extrato */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="statement_date">Data do Extrato</Label>
                                    <Input
                                        id="statement_date"
                                        type="date"
                                        value={data.statement_date}
                                        onChange={(e) => setData('statement_date', e.target.value)}
                                        required
                                    />
                                    {errors.statement_date && (
                                        <p className="text-sm text-red-600">{errors.statement_date}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="statement_type">Tipo de Extrato</Label>
                                    <Select
                                        value={data.statement_type}
                                        onValueChange={(value: 'csv' | 'ofx' | 'pdf' | 'manual') =>
                                            setData('statement_type', value)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="csv">CSV</SelectItem>
                                            <SelectItem value="ofx">OFX</SelectItem>
                                            <SelectItem value="pdf">PDF</SelectItem>
                                            <SelectItem value="manual">Manual</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.statement_type && (
                                        <p className="text-sm text-red-600">{errors.statement_type}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="original_filename">Nome do Arquivo Original (opcional)</Label>
                                <Input
                                    id="original_filename"
                                    value={data.original_filename}
                                    onChange={(e) => setData('original_filename', e.target.value)}
                                    placeholder="extrato_banco_2024_01.csv"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="notes">Observações (opcional)</Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Observações sobre este extrato..."
                                    rows={3}
                                />
                            </div>

                            {/* Itens do Extrato */}
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-lg font-semibold">Itens do Extrato</h3>
                                    <Button type="button" onClick={addItem} variant="outline" size="sm">
                                        Adicionar Item
                                    </Button>
                                </div>

                                {items.map((item, index) => (
                                    <Card key={item.id} className="border-dashed">
                                        <CardContent className="pt-6">
                                            <div className="grid grid-cols-1 md:grid-cols-6 gap-4">
                                                <div className="md:col-span-2 space-y-2">
                                                    <Label htmlFor={`item-${index}-date`}>Data</Label>
                                                    <Input
                                                        id={`item-${index}-date`}
                                                        type="date"
                                                        value={item.date}
                                                        onChange={(e) => updateItem(index, 'date', e.target.value)}
                                                        required
                                                    />
                                                </div>

                                                <div className="md:col-span-2 space-y-2">
                                                    <Label htmlFor={`item-${index}-description`}>Descrição</Label>
                                                    <Input
                                                        id={`item-${index}-description`}
                                                        value={item.description}
                                                        onChange={(e) => updateItem(index, 'description', e.target.value)}
                                                        placeholder="Descrição da transação"
                                                        required
                                                    />
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor={`item-${index}-amount`}>Valor</Label>
                                                    <Input
                                                        id={`item-${index}-amount`}
                                                        type="number"
                                                        step="0.01"
                                                        value={item.amount}
                                                        onChange={(e) => updateItem(index, 'amount', e.target.value)}
                                                        placeholder="0.00"
                                                        required
                                                    />
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor={`item-${index}-type`}>Tipo</Label>
                                                    <Select
                                                        value={item.type}
                                                        onValueChange={(value: 'credit' | 'debit') =>
                                                            updateItem(index, 'type', value)
                                                        }
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="credit">Crédito</SelectItem>
                                                            <SelectItem value="debit">Débito</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>

                                                <div className="md:col-span-2 space-y-2">
                                                    <Label htmlFor={`item-${index}-bank_reference`}>
                                                        Referência Bancária (opcional)
                                                    </Label>
                                                    <Input
                                                        id={`item-${index}-bank_reference`}
                                                        value={item.bank_reference || ''}
                                                        onChange={(e) => updateItem(index, 'bank_reference', e.target.value)}
                                                        placeholder="Código do banco"
                                                    />
                                                </div>

                                                <div className="md:col-span-3 space-y-2">
                                                    <Label htmlFor={`item-${index}-notes`}>
                                                        Observações (opcional)
                                                    </Label>
                                                    <Input
                                                        id={`item-${index}-notes`}
                                                        value={item.notes || ''}
                                                        onChange={(e) => updateItem(index, 'notes', e.target.value)}
                                                        placeholder="Observações sobre este item"
                                                    />
                                                </div>

                                                <div className="md:col-span-1 flex items-end">
                                                    {items.length > 1 && (
                                                        <Button
                                                            type="button"
                                                            onClick={() => removeItem(index)}
                                                            variant="destructive"
                                                            size="sm"
                                                        >
                                                            Remover
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>

                            {errors.items && (
                                <p className="text-sm text-red-600">{errors.items}</p>
                            )}

                            <div className="flex justify-end gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    asChild
                                    disabled={processing}
                                >
                                    <Link href={route('bank-reconciliation.show', bank_account.id)}>
                                        Cancelar
                                    </Link>
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Importando...' : 'Importar Extrato'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}