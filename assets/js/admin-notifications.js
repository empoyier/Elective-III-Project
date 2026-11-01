function initAdminBookingNotifications() {
    const client = typeof supabaseClient !== 'undefined'
        ? supabaseClient
        : typeof _supabase !== 'undefined'
            ? _supabase
            : null;
    const badge = document.getElementById('pending-bookings-count');

    if (!client || !badge) return;

    async function refreshPendingCount() {
        let count;
        try {
            const result = await client
                .from('bookings')
                .select('id', { count: 'exact', head: true })
                .eq('status', 'pending');

            if (result.error) {
                console.error('Unable to load pending reservation count:', result.error);
                return;
            }

            count = result.count;
        } catch (error) {
            console.error('Unable to load pending reservation count:', error);
            return;
        }

        const total = count || 0;
        badge.textContent = total > 99 ? '99+' : String(total);
        badge.setAttribute('aria-label', `${total} pending reservations`);
        badge.hidden = !count;
    }

    refreshPendingCount();
    client
        .channel('admin-pending-reservations')
        .on('postgres_changes', {
            event: '*',
            schema: 'public',
            table: 'bookings'
        }, refreshPendingCount)
        .subscribe((status) => {
            if (status === 'CHANNEL_ERROR' || status === 'TIMED_OUT') {
                console.error('Unable to subscribe to reservation updates:', status);
            }
        });

    window.setInterval(refreshPendingCount, 30000);
}

document.addEventListener('DOMContentLoaded', initAdminBookingNotifications);
