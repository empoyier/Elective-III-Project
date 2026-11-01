-- Run once in the Supabase SQL Editor to enable admin vehicle image uploads.
-- Admin/staff upload permissions use the trusted app_metadata.role JWT claim.
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'vehicle-images',
    'vehicle-images',
    TRUE,
    5242880,
    ARRAY['image/jpeg', 'image/png', 'image/webp', 'image/avif']
)
ON CONFLICT (id) DO UPDATE
SET public = EXCLUDED.public,
    file_size_limit = EXCLUDED.file_size_limit,
    allowed_mime_types = EXCLUDED.allowed_mime_types;

DROP POLICY IF EXISTS "Public can view vehicle images" ON storage.objects;
CREATE POLICY "Public can view vehicle images"
    ON storage.objects
    FOR SELECT
    TO public
    USING (bucket_id = 'vehicle-images');

DROP POLICY IF EXISTS "Authenticated users can upload vehicle images" ON storage.objects;
CREATE POLICY "Authenticated users can upload vehicle images"
    ON storage.objects
    FOR INSERT
    TO authenticated
    WITH CHECK (
        bucket_id = 'vehicle-images'
        AND (auth.jwt() -> 'app_metadata' ->> 'role') IN ('admin', 'staff')
    );

DROP POLICY IF EXISTS "Authenticated users can remove vehicle images" ON storage.objects;
CREATE POLICY "Authenticated users can remove vehicle images"
    ON storage.objects
    FOR DELETE
    TO authenticated
    USING (
        bucket_id = 'vehicle-images'
        AND (auth.jwt() -> 'app_metadata' ->> 'role') IN ('admin', 'staff')
    );
