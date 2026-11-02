# APEX Car Rental Services

APEX Car Rental Services is a static, multi-page car rental website. Customers can browse vehicles, create an account, and manage bookings. Administrators can use the dashboard to manage vehicles, bookings, and customer records. The frontend uses Supabase for authentication, database access, and vehicle image storage.

## Deploy on Vercel

1. Import this GitHub repository into Vercel.
2. Leave the project root directory set to the repository root. Vercel reads `vercel.json` to run `node scripts/build.mjs` and publish the generated `dist` directory.
3. Deploy the project. The build includes the root HTML pages, `admin/`, and `assets/`. It does not deploy the legacy PHP backup or the database scripts.

The current frontend does not require build-time environment variables or package installation. To use a different Supabase project, update `assets/js/supabase-config.js` and the Supabase configuration in `admin/bookings.html` and `admin/customers.html`. Browser code must only contain a Supabase publishable/anon key; never put a service-role key in the frontend.

## Supabase setup

- Apply `database/supabase_schema.sql` in the Supabase SQL Editor to create the tables and demo data.
- Apply `database/vehicle_image_storage.sql` if admin vehicle image uploads are needed.
- Set the Supabase Auth Site URL to your deployed Vercel URL and add the production URL (and any custom domain) to the redirect URL allowlist.

**Demo deployment only:** the included schema grants unrestricted anonymous access to application tables. Do not use it with real customer data or treat frontend admin checks as authorization. Restrict the Supabase Row Level Security policies and enforce admin permissions server-side before production use.

## Features

- **Browse vehicles:** View the fleet, photos, and rental details.
- **User accounts:** Sign up and log in through Supabase Authentication.
- **Online booking:** Reserve vehicles and view bookings.
- **Admin dashboard:** Manage vehicles (including image uploads), bookings, and customers.
