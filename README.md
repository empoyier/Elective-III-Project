# APEX Car Rental Services

APEX Car Rental Services is a static, multi-page car rental website I built. Customers can browse vehicles, create an account, and manage their bookings. Administrators can use a dashboard to manage vehicles, bookings, and customer records.

## Tech Stack and How I Use Each Tool

- **GitHub:** I use GitHub for version control and as the source of the project. Every change I make is pushed to the repository, and Vercel deploys directly from it.
- **Vercel:** I use Vercel to host the website. It reads `vercel.json`, runs `node scripts/build.mjs`, and publishes the generated `dist` directory. The build includes the root HTML pages, `admin/`, and `assets/`. I left out the legacy PHP backup and the database scripts on purpose.
- **Supabase:** I use Supabase as the backend. It handles authentication, the database, and vehicle image storage, so the frontend can stay static.
- **Google Cloud (OAuth):** I use Google OAuth so customers can sign in with their Google account.

The frontend does not need build-time environment variables or package installation. The browser code only contains the Supabase publishable/anon key. I never put a service-role key in the frontend.

## How I Set Up the Backend

### Database

- I used `database/supabase_schema.sql` in the Supabase SQL Editor to create the tables and demo data.
- For booking notifications and vehicle reviews, I wrote a separate migration, `database/migrations/20261103230000_add_reviews_and_notifications.sql`. I made it separate so it can be applied to an existing project without rerunning the demo seed script or the booking-overlap constraint.
- For admin vehicle image uploads, I used `database/vehicle_image_storage.sql`.

### Authentication

- I set the Supabase Auth Site URL to my deployed Vercel URL and added the production URL to the redirect allowlist.
- I enabled Google sign-in in Supabase under **Authentication → Sign In / Providers → Google**.
- In Google Cloud Console, I created a web OAuth client ID and added the Supabase callback URL to its **Authorized redirect URIs**. Then I pasted the client ID and secret into Supabase.
- The Google button appears on both the sign-in and registration pages. Google accounts are added to the customer profile table on first sign-in. Phone and driver's-license details are collected later, when the customer books.

### Switching Supabase Projects

To point the site at a different Supabase project, I update `assets/js/supabase-config.js` plus the Supabase configuration in `admin/bookings.html` and `admin/customers.html`.

## Features I Built

- **Browse vehicles:** View the fleet, photos, and rental details.
- **User accounts:** Sign up and log in through Supabase Authentication, including Google sign-in.
- **Online booking:** Reserve vehicles and view bookings.
- **Date-based availability:** Filter vehicles by rental dates. Overlapping reservations and scheduled maintenance are blocked.
- **In-app booking notifications:** Customers see when a reservation is submitted or its status changes.
- **Verified vehicle reviews:** Customers can rate and review a vehicle only after their booking is returned.
- **Admin dashboard:** Manage vehicles (including image uploads), bookings, and customers.

The availability constraints, notifications, reviews, and their database triggers are all defined in `database/supabase_schema.sql`.

## Known Limitation (Demo Deployment Only)

This is a demo deployment. The schema I included grants unrestricted anonymous access to the application tables, and the admin checks only happen in the frontend. It should not be used with real customer data. Before any production use, I would restrict the Row Level Security policies and enforce admin permissions server-side.

## Links

- Website: https://apex-car-rental-steel.vercel.app/
- GitHub Repo: https://github.com/empoyier/Elective-III-Project
- Supabase: https://ybwsiaieehefoceueqdl.supabase.co
