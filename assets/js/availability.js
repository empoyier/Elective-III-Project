async function getUnavailableVehicleIds(pickupValue, returnValue) {
    const pickup = new Date(pickupValue);
    const returnDate = new Date(returnValue);

    if (Number.isNaN(pickup.getTime()) || Number.isNaN(returnDate.getTime()) || returnDate <= pickup) {
        throw new Error('Choose a valid pickup and return date/time.');
    }

    const pickupIso = pickup.toISOString();
    const returnIso = returnDate.toISOString();
    const pickupDay = pickupValue.slice(0, 10);
    const returnDay = returnValue.slice(0, 10);

    const [bookingsResult, maintenanceResult] = await Promise.all([
        supabaseClient
            .from('bookings')
            .select('vehicle_id')
            .in('status', ['pending', 'approved', 'out_on_rental'])
            .lt('pickup_datetime', returnIso)
            .gt('return_datetime', pickupIso),
        supabaseClient
            .from('maintenance')
            .select('vehicle_id')
            .in('status', ['scheduled', 'in_progress'])
            .lte('start_date', returnDay)
            .gte('end_date', pickupDay)
    ]);

    if (bookingsResult.error) throw bookingsResult.error;
    if (maintenanceResult.error) throw maintenanceResult.error;

    return new Set([
        ...(bookingsResult.data || []).map(booking => Number(booking.vehicle_id)),
        ...(maintenanceResult.data || []).map(record => Number(record.vehicle_id))
    ]);
}
