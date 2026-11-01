# APEX Car Rental Services
APEX Car Rental Services is a web-based car rental platform that makes it easy to find, book, and manage vehicle rentals online. Customers can browse the available fleet with photos and details, create an account, and reserve a vehicle for their chosen dates. Administrators get a dedicated dashboard for managing vehicles, bookings, and customer records. Static, multi-page frontend (HTML, CSS, JavaScript) deployed on Netlify. The application uses Supabase for authentication, database access, and vehicle image storage.

## Deploy to Netlify

1. Push this repository to GitHub and import it in Netlify.
2. Keep the build settings from `netlify.toml`: the build command is `node scripts/build.mjs` and the publish directory is `dist`.
3. Deploy. The build includes the site's HTML pages, `admin/`, and `assets/`; it excludes the PHP archive, database scripts, and local preview.

No frontend packages or environment variables are required for the current configuration. To point the site at another Supabase project, update `assets/js/supabase-config.js` and the Supabase configuration in `admin/bookings.html` and `admin/customers.html`. Only use a Supabase publishable/anon key in browser code—never a service-role key.

## Supabase setup

- Apply `database/supabase_schema.sql` in the Supabase SQL Editor to create the tables and demo data.
- Apply `database/vehicle_image_storage.sql` if admin vehicle image uploads are needed.
- Set the Supabase Auth Site URL to the deployed Netlify URL and add the production URL (and any custom domain) to its redirect URL allowlist.

**Demo deployment only:** the included schema grants unrestricted anonymous access to application tables. Do not use it with real customer data or treat the frontend's admin checks as authorization. Restrict the Supabase Row Level Security policies and enforce admin permissions server-side before production use.

## Features

- **Browse vehicles:** View the fleet with images and rental details.
- **User accounts:** Sign up and log in securely through Supabase Authentication.
- **Online booking:** Reserve a vehicle and track your bookings.
- **Admin dashboard:** Manage vehicles (including image uploads), bookings, and customers.


