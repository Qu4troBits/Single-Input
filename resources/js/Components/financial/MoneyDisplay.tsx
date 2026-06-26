import { formatBRL } from '@/Utils/formatCurrency';

interface MoneyDisplayProps {
  amount: string | number;
  direction?: 'in' | 'out';
  showSign?: boolean;
  className?: string;
}

export function MoneyDisplay({ 
  amount, 
  direction,
  showSign = true,
  className = '' 
}: MoneyDisplayProps) {
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  const isNegative = numericAmount < 0;
  const isIncome = direction === 'in';
  const isExpense = direction === 'out';

  const getColorClass = () => {
    if (direction) {
      return isIncome ? 'text-green-600' : 'text-red-600';
    }
    return isNegative ? 'text-red-600' : 'text-green-600';
  };

  const getSign = () => {
    if (!showSign) return '';
    
    if (direction) {
      return isIncome ? '+' : '-';
    }
    
    return isNegative ? '-' : '+';
  };

  const getDisplayAmount = () => {
    const absoluteAmount = Math.abs(numericAmount);
    return formatBRL(absoluteAmount.toString());
  };

  return (
    <span className={`font-semibold ${getColorClass()} ${className}`}>
      {getSign()}
      {getDisplayAmount()}
    </span>
  );
}