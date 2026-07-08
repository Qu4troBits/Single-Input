export function formatBRL(amount: string | number): string {
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numericAmount);
}

export function formatCurrency(amount: string | number): string {
  return formatBRL(amount);
}
