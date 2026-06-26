import { Badge } from '@/Components/ui/badge';
import { CheckCircle, Clock, XCircle, AlertCircle } from 'lucide-react';

export type TransactionStatus = 'pending' | 'paid' | 'cancelled' | 'reversed';

interface TransactionStatusBadgeProps {
  status: TransactionStatus;
  showIcon?: boolean;
  className?: string;
}

export function TransactionStatusBadge({ 
  status, 
  showIcon = true,
  className = '' 
}: TransactionStatusBadgeProps) {
  const getStatusConfig = () => {
    switch (status) {
      case 'paid':
        return {
          variant: 'success' as const,
          label: 'Pago',
          icon: <CheckCircle className="h-3 w-3" />,
          color: 'text-green-500',
        };
      case 'pending':
        return {
          variant: 'warning' as const,
          label: 'Pendente',
          icon: <Clock className="h-3 w-3" />,
          color: 'text-yellow-500',
        };
      case 'cancelled':
        return {
          variant: 'destructive' as const,
          label: 'Cancelado',
          icon: <XCircle className="h-3 w-3" />,
          color: 'text-red-500',
        };
      case 'reversed':
        return {
          variant: 'secondary' as const,
          label: 'Estornado',
          icon: <AlertCircle className="h-3 w-3" />,
          color: 'text-orange-500',
        };
    }
  };

  const config = getStatusConfig();

  return (
    <Badge variant={config.variant} className={`flex items-center gap-1 ${className}`}>
      {showIcon && <span className={config.color}>{config.icon}</span>}
      <span>{config.label}</span>
    </Badge>
  );
}