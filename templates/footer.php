<?php
/**
 * APEX Car Rental Management System
 * Shared Footer Template
 */
?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <span class="brand-badge">APEX</span>
                <span style="font-size:16px; font-weight:700; color:#FFFFFF;">Car Rental Services</span>
            </div>
            <p style="font-size:13px; line-height:1.6; color:#FFFFFF; margin-bottom:14px;">
                A web-based car rental management system delivering streamlined vehicle reservations, date-based availability conflict prevention, and simulated real-time GPS vehicle tracking across the Philippines.
            </p>
            <p style="font-size:12px; color:#FFFFFF;">
                Telemetry Notice: All map movement and telemetry values (speed, distance, fuel) are generated via route simulation and do not require physical GPS hardware.
            </p>
        </div>

        <div class="footer-col">
            <h4>Fleet Categories</h4>
            <ul>
                <li><a href="<?= apex_url('fleet.php?category=economy') ?>">Economy & Subcompact</a></li>
                <li><a href="<?= apex_url('fleet.php?category=sedan') ?>">Executive Sedans</a></li>
                <li><a href="<?= apex_url('fleet.php?category=suv') ?>">Family SUVs & 4x4s</a></li>
                <li><a href="<?= apex_url('fleet.php?category=van-mpv') ?>">Passenger Vans & MPVs</a></li>
                <li><a href="<?= apex_url('fleet.php?category=luxury') ?>">Luxury Saloons</a></li>
                <li><a href="<?= apex_url('fleet.php?category=sports') ?>">Sports & Performance</a></li>
                <li><a href="<?= apex_url('fleet.php?category=electric') ?>">Modern Electric Vehicles</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= apex_url('fleet.php') ?>">Browse All Vehicles</a></li>
                <li><a href="<?= apex_url('my-bookings.php') ?>">My Reservations</a></li>
                <li><a href="<?= apex_url('track.php') ?>">Live Simulated Tracking</a></li>
                <li><a href="<?= apex_url('login.php') ?>">Client Sign In</a></li>
                <li><a href="<?= apex_url('admin/index.php') ?>">Fleet Operations Portal</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Philippine Hubs</h4>
            <p style="font-size:13px; margin-bottom:6px; color:#CBD5E1;"><strong>Iba, Zambales Hub:</strong> Main Rental Hub</p>
            
            <p style="font-size:13px; margin-bottom:12px; color:#CBD5E1;"><strong>Visayas:</strong> Mactan-Cebu Airport Hub (MCIA)</p>
            <p style="font-size:12px; color:#64748B;">
                Hotline: +63 (02) 8800-APEX<br>
                Email: reservations@apexrentals.ph
            </p>
        </div>
    </div>

    <div class="container footer-bottom">
        <div>&copy; <?= date('Y') ?> APEX Car Rental Services. All rights reserved. Built for Elective III Project.</div>
        <div>Philippine Fleet Operations &bull; Windows BSoD Design System</div>
    </div>
</footer>

<script src="<?= apex_url('assets/js/main.js') ?>"></script>
</body>
</html>
