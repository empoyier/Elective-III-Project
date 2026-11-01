-- Repair the Supabase relationship used by admin booking queries:
-- .select('*, users(name,email,phone)')
-- Run after supabase_schema.sql on an existing project.

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'bookings_user_id_fkey'
          AND conrelid = 'public.bookings'::regclass
    ) THEN
        ALTER TABLE public.bookings
            ADD CONSTRAINT bookings_user_id_fkey
            FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;
    END IF;
END $$;

-- Make PostgREST refresh its relationship cache immediately.
NOTIFY pgrst, 'reload schema';
