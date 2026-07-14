export function formatDate(date: string | Date, locale: string = 'pt-BR'): string {
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  return dateObj.toLocaleDateString(locale);
}

export function formatDateTime(date: string | Date, locale: string = 'pt-BR'): string {
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  return dateObj.toLocaleString(locale);
}