export function formatDateIndonesia(dateString) {
    if (!dateString) return '-';
    
    try {
        // Parse yyyy-mm-dd format
        const date = new Date(dateString);
        
        if (isNaN(date.getTime())) return dateString;
        
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        const dayName = days[date.getDay()];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${dayName}, ${day} ${month} ${year}`;
    } catch (e) {
        return dateString;
    }
}
