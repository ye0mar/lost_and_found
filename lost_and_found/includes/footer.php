<?php
/**
 * Application Common Footer Component
 * Lost & Found Management System
 */

declare(strict_types=1);

$orgName = get_system_setting('organization_name', 'Campus Security & Property Custody');
$sysName = get_system_setting('system_name', 'Lost & Found Management System');
$logoUrl = get_logo_url();
?>
    </div><!-- End content-wrapper -->
    
    <footer style="margin-top: auto; background: #FFFFFF; border-top: 1px solid var(--border-color); padding: 24px 28px;">
        <div style="max-width: 1400px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 6px; overflow: hidden; background: #172033; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                    <img src="<?= e($logoUrl) ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 0;">
                </div>
                <div>
                    <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);"><?= e($sysName) ?></div>
                    <div style="font-size: 11.5px; color: var(--text-secondary);"><?= e($orgName) ?></div>
                </div>
            </div>

            <div style="font-size: 12px; color: var(--text-muted);">
                &copy; <?= date('Y') ?> System Administration &amp; Maintenance Capstone. All rights reserved.
            </div>
        </div>
    </footer>
    </div><!-- End app-main -->
</div><!-- End app-layout -->

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
