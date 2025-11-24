export function formatDate(value: string | null | undefined): string {
  if (!value) return '-';
  try {
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value as string;
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(d);
  } catch (e) {
    return String(value);
  }
}
