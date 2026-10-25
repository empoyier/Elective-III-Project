<?php
/**
 * APEX Car Rental Management System
 * Opening Landing Page (Minimalist 50/50 Screen: Cartoon Cars & "Get Started", No Navbar)
 */

require_once __DIR__ . '/config/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= apex_url('assets/images/favicon.png') ?>">
    <title>APEX Car Rental Services | Welcome</title>
    <link rel="stylesheet" href="<?= apex_url('assets/css/apex-theme.css') ?>">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #FFFFFF;
            font-family: var(--apex-font-sans);
        }

        .splash-split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
            width: 100vw;
        }

        /* LEFT HALF: Cartoon Cars Illustration */
        .splash-media-half {
            height: 100vh;
            background: #CBD5E1;
            border-right: 2px solid var(--apex-border-crisp);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .splash-media-half img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* RIGHT HALF: Headline, Info, and "Get Started" Button */
        .splash-content-half {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 80px;
            background-color: #FFFFFF;
            position: relative;
        }

        .splash-brand-logo {
            display: block;
            width: min(100%, 560px);
            max-width: 100%;
            height: auto;
            margin-bottom: 16px;
            align-self: flex-start;
        }

        .splash-title {
            font-size: 46px;
            font-weight: 900;
            line-height: 1.15;
            color: var(--apex-text-main);
            letter-spacing: -0.5px;
            margin-bottom: 18px;
        }

        .splash-subtitle {
            font-size: 17px;
            line-height: 1.6;
            color: var(--apex-text-muted);
            margin-bottom: 40px;
            max-width: 520px;
        }

        .splash-btn-get-started {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background-color: var(--apex-primary);
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 800;
            padding: 16px 36px;
            border-radius: var(--apex-radius);
            text-decoration: none;
            transition: background-color 0.15s ease, transform 0.1s ease;
            align-self: flex-start;
            border: 2px solid var(--apex-primary);
            box-shadow: 0 4px 14px rgba(0, 120, 215, 0.25);
        }

        .splash-btn-get-started:hover {
            background-color: var(--apex-primary-hover);
            border-color: var(--apex-primary-hover);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .splash-bottom-bar {
            position: absolute;
            bottom: 30px;
            left: 80px;
            right: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: var(--apex-text-muted);
            border-top: 1px solid var(--apex-border-subtle);
            padding-top: 16px;
        }

        .splash-bottom-bar a {
            color: var(--apex-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .splash-bottom-bar a:hover {
            text-decoration: underline;
        }

        /* Responsive collapse for tablets & mobile */
        @media (max-width: 900px) {
            html, body {
                overflow: auto;
                height: auto;
            }
            .splash-split-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            .splash-media-half {
                height: 890px;
                border-right: none;
                border-bottom: 2px solid var(--apex-border-crisp);
            }
            .splash-content-half {
                height: auto;
                padding: 40px 24px 60px;
            }
            .splash-title {
                font-size: 32px;
            }
            .splash-bottom-bar {
                position: static;
                margin-top: 40px;
                left: auto;
                right: auto;
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="splash-split-container">
    <!-- LEFT HALF: Cartoon Cars Illustration (Takes 50% width) -->
    <div class="splash-media-half">
        <img src="<?= apex_url('assets/images/cartoon-cars.svg') ?>" alt="APEX Cartoon Cars Illustration">
    </div>

    <!-- RIGHT HALF: Minimal Title, Tagline & "Get Started" Button (No Navbar) -->
    <div class="splash-content-half">
        <img class="splash-brand-logo" src="<?= apex_url('assets/images/apex-brand-logo.png') ?>" alt="APEX Car Rental logo">

        <p class="splash-subtitle">
            Reliable, transparent vehicle rentals across Iba, Zambales. 
            Enjoy date-guaranteed availability, straightforward Philippine Peso rates, 
            and simulated real-time GPS fleet telemetry.
        </p>

        <!-- Big, prominent "Get Started" button leading directly to the car selection page -->
        <a href="<?= apex_url('fleet.php') ?>" class="splash-btn-get-started">
            Get Started &rarr;
        </a>

        <!-- Subtle footer links for sign in and admin -->
        <div class="splash-bottom-bar">
            <span>Philippine Fleet Operations &bull; 21 Available Models</span>
            <div>
                Returning client or staff? <a href="<?= apex_url('login.php') ?>">Sign In</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
