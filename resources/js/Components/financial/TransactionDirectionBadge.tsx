import { Badge } from '@/Components/ui/badge';
import { ArrowUpRight, ArrowDownRight } from 'lucide-react';

export type TransactionDirection = 'in' | 'out';

interface TransactionDirectionBadgeProps {
  direction: TransactionDirection;
  showIcon?: boolean;
  className?: string;
}

export function TransactionDirectionBadge({ 
  direction, 
  showIcon = true,
  className = '' 
}: TransactionDirectionBadgeProps) {
  const getDirectionConfig = () => {
    switch (direction) {
      case 'in':
        return {
          variant: 'success' as const,
          label: 'Receita',
          icon: <ArrowUpRight className="h-3 w-3" />,
          color: 'text-green-500',
        };
      case 'out':
        return {
          variant: 'destructive' as const,
          label: 'Despesa',
          icon: <ArrowDownRight className="h-3 w-3" />,
          color: 'text-red-500',
        };
    }
  };

  const config = getDirectionConfig();

  return (
    <Badge variant={config.variant} className={`flex items-center gap-1 ${className}`}>
      {showIcon && <span className={config.color}>{config.icon}</span>}
      <span>{config.label}</span>
    </Badge>
  );
}