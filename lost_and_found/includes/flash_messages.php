<?php
/**
 * Flash Notifications Renderer
 */

declare(strict_types=1);

if (!function_exists('get_flash_messages')) {
    require_once __DIR__ . '/functions.php';
}

$flashes = get_flash_messages();
if (!empty($flashes)): ?>
    <div class="flash-container">
        <?php foreach ($flashes as $flash): ?>
            <div class="flash-alert <?= e($flash['type']) ?>" role="alert">
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php if ($flash['type'] === 'success'): ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php elseif ($flash['type'] === 'error'): ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php else: ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <?php endif; ?>
                    <span><?= e($flash['message']) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:inherit;opacity:0.7;">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
