import { CategoryType, CategoryStatus } from '@/types/category';

/**
 * Retorna o rótulo legível para o tipo de categoria
 */
export function getCategoryTypeLabel(type: string): string {
  const map: Record<string, string> = {
    revenue: 'Receita',
    expense: 'Despesa',
    transfer: 'Transferência',
  };
  return map[type] || type;
}

/**
 * Retorna o rótulo legível para o status da categoria
 */
export function getCategoryStatusLabel(status: string): string {
  const map: Record<string, string> = {
    active: 'Ativo',
    inactive: 'Inativo',
    archived: 'Arquivado',
  };
  return map[status] || status;
}

/**
 * Retorna a cor (classe Tailwind) para o badge do status
 */
export function getCategoryStatusColor(status: string): string {
  const map: Record<string, string> = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    archived: 'bg-red-100 text-red-800',
  };
  return map[status] || 'bg-gray-100 text-gray-800';
}