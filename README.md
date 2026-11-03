# APEX Car Rental Services

APEX Car Rental Services is a static, multi-page car rental website. Customers can browse vehicles, create an account, and manage bookings. Administrators can use the dashboard to manage vehicles, bookings, and customer records. The frontend uses Supabase for authentication, database access, and vehicle image storage.

## Deploy on Vercel

1. Import this GitHub repository into Vercel.
2. Leave the project root directory set to the repository root. Vercel reads `vercel.json` to run `node scripts/build.mjs` and publish the generated `dist` directory.
3. Deploy the project. The build includes the root HTML pages, `admin/`, and `assets/`. It does not deploy the legacy PHP backup or the database scripts.

The current frontend does not require build-time environment variables or package installation. To use a different Supabase project, update `assets/js/supabase-config.js` and the Supabase configuration in `admin/bookings.html` and `admin/customers.html`. Browser code must only contain a Supabase publishable/anon key; never put a service-role key in the frontend.

## Supabase setup

- Apply `database/supabase_schema.sql` in the Supabase SQL Editor to create the tables and demo data.
- For an existing project, apply `database/migrations/20261103230000_add_reviews_and_notifications.sql` to add booking notifications and vehicle reviews without rerunning the demo seed script or booking-overlap constraint.
- Apply `database/vehicle_image_storage.sql` if admin vehicle image uploads are needed.
- Set the Supabase Auth Site URL to your deployed Vercel URL and add the production URL (and any custom domain) to the redirect URL allowlist.

### Enable Google sign-in

1. In Supabase, open **Authentication → Sign In / Providers → Google** and enable Google. Its callback URL is `https://ybwsiaieehefoceueqdl.supabase.co/auth/v1/callback`.
2. In Google Cloud Console, create a Google OAuth client ID for a web application. Add that Supabase callback URL to its **Authorized redirect URIs**.
3. Paste the Google client ID and client secret into the Supabase Google provider settings and save.
4. In Supabase **Authentication → URL Configuration**, set the Site URL to `https://apex-empoy.vercel.app` and add `https://apex-empoy.vercel.app/**` to the allowed redirect URLs.

The Google button is available on both the sign-in and registration pages. Google accounts are added to the customer profile table on their first sign-in; phone and driver's-license details can be provided later when booking.

**Demo deployment only:** the included schema grants unrestricted anonymous access to application tables. Do not use it with real customer data or treat frontend admin checks as authorization. Restrict the Supabase Row Level Security policies and enforce admin permissions server-side before production use.

## Features

- **Browse vehicles:** View the fleet, photos, and rental details.
- **User accounts:** Sign up and log in through Supabase Authentication.
- **Online booking:** Reserve vehicles and view bookings.
- **Date-based availability:** Filter vehicles by rental dates; overlapping reservations and scheduled maintenance are blocked.
- **In-app booking notifications:** See when a reservation is submitted or its status changes.
- **Verified vehicle reviews:** Customers can rate and review vehicles after a booking is returned.
- **Admin dashboard:** Manage vehicles (including image uploads), bookings, and customers.

Availability constraints, notifications, reviews, and their database triggers are defined in `database/supabase_schema.sql`. The migration under `database/migrations/` can be used to apply notifications and reviews independently when an existing project's bookings prevent adding the availability constraint.

## Linksss :>
- Website URL:  https://apex-empoy.vercel.app/
- Github Repo:  https://github.com/empoyier/Elective-III-Project
- Supabase:     https://ybwsiaieehefoceueqdl.supabase.cohttps://ybwsiaieehefoceueqdl.supabase.co