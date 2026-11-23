<?php
/**
 * Blocking screen shown to the super administrator while the installer
 * directory is still on the server. Included from admin/index.php after the
 * session checks; it handles its own form and never returns to the caller.
 */

use SLiMS\InstallerGuard;

if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
}

if (empty($_SESSION['installer_gate_token'])) {
    $_SESSION['installer_gate_token'] = bin2hex(random_bytes(24));
}

$gateError = '';
if (isset($_POST['removeInstaller'])) {
    if (!hash_equals($_SESSION['installer_gate_token'], (string)($_POST['gateToken'] ?? ''))) {
        $gateError = __('Invalid form submission token!');
    } else {
        $gateResult = InstallerGuard::remove();
        if ($gateResult['ok']) {
            unset($_SESSION['installer_gate_token']);
            $_SESSION['installer_removed_notice'] = true;
            header('Location: ' . AWB . 'index.php');
            exit;
        }
        // could not delete: at least make the installer refuse to run
        InstallerGuard::lock();
        $gateError = $gateResult['reason'];
    }
}

// removed by hand in the meantime (the "Check again" button lands here)
if (!InstallerGuard::exists()) {
    header('Location: ' . AWB . 'index.php');
    exit;
}

$gatePath = InstallerGuard::path();
$gateSelf = AWB . 'index.php';
$e = function ($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= $e(__('One more step before you continue')) ?></title>
    <link href="<?= SWB ?>css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body { background: #f4f6f8; }
        .gate { max-width: 640px; margin: 8vh auto; padding: 0 16px; }
        .gate .card { border: 0; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); }
        .gate pre { background: #f1f3f5; padding: 10px 12px; border-radius: 4px; white-space: pre-wrap; word-break: break-all; margin-bottom: 8px; }

        /* folder tree illustration */
        .gate-tree { display: block; width: 100%; max-width: 360px; margin: 4px auto 20px; }
        .gate-name { font: 14px/1 SFMono-Regular, Menlo, Consolas, monospace; fill: #495057; }
        .gate-root { font-weight: 700; fill: #212529; }
        .gate-line { fill: none; stroke: #ced4da; stroke-width: 1.5; }
        .gate-folder { fill: #adb5bd; }
        .gate-check { stroke: #28a745; }
        .gate-target-bg { fill: #dc3545; opacity: .1; animation: gate-bg 4.5s ease-in-out infinite; }
        .gate-folder-target { fill: #dc3545; }
        .gate-name-target { fill: #dc3545; font-weight: 700; }
        .gate-target { animation: gate-fade 4.5s ease-in-out infinite; }
        .gate-strike { fill: none; stroke: #dc3545; stroke-width: 2; stroke-linecap: round; stroke-dasharray: 76; stroke-dashoffset: 76; animation: gate-strike 4.5s ease-in-out infinite; }
        .gate-trash { stroke: #dc3545; animation: gate-trash 4.5s ease-in-out infinite; transform-origin: 334px 136px; }

        /* 0-25% shown, 25-45% crossed out, 45-65% fades away, then stays gone until the loop restarts */
        @keyframes gate-strike { 0%, 25% { stroke-dashoffset: 76; } 45%, 92% { stroke-dashoffset: 0; } 100% { stroke-dashoffset: 76; } }
        @keyframes gate-fade { 0%, 45% { opacity: 1; } 65%, 92% { opacity: 0; } 100% { opacity: 1; } }
        @keyframes gate-bg { 0%, 45% { opacity: .1; } 65%, 92% { opacity: 0; } 100% { opacity: .1; } }
        @keyframes gate-trash { 0%, 40% { transform: scale(1); } 50% { transform: scale(1.35); } 60%, 100% { transform: scale(1); } }

        /* no motion: show the end state of the folder, crossed out */
        @media (prefers-reduced-motion: reduce) {
            .gate-target, .gate-target-bg, .gate-trash { animation: none; }
            .gate-strike { animation: none; stroke-dashoffset: 0; }
        }
    </style>
</head>
<body>
<div class="gate">
    <div class="card">
        <div class="card-body p-4">
            <h4 class="mb-3"><?= $e(__('One more step before you continue')) ?></h4>
            <p>
                <?= $e(__('The installer folder is still on this server. Anyone who can open it may be able to take over this site, so it has to be removed before you use the administration area.')) ?>
            </p>

            <!-- what gets removed: only the install folder, everything else stays -->
            <svg class="gate-tree" viewBox="0 0 360 196" role="img" aria-label="<?= $e(__('Only the install folder is removed. Other folders stay.')) ?>">
                <defs>
                    <symbol id="gate-folder" viewBox="0 0 20 16">
                        <path d="M1 3a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2z"/>
                    </symbol>
                    <symbol id="gate-check" viewBox="0 0 16 16">
                        <path d="M3 8.5l3.2 3.2L13 4.8" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </symbol>
                    <symbol id="gate-trash" viewBox="0 0 16 16">
                        <path d="M2.5 4h11M6 4V2.5h4V4M4 4l.7 9.5h6.6L12 4M6.6 6.5v5M9.4 6.5v5" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </symbol>
                </defs>

                <!-- root -->
                <use href="#gate-folder" x="8" y="8" width="20" height="16" class="gate-folder"/>
                <text x="36" y="21" class="gate-name gate-root">slims</text>
                <path d="M18 28v142" class="gate-line"/>

                <?php foreach (['admin' => 44, 'config' => 72, 'files' => 100] as $gateName => $gateY): ?>
                <path d="M18 <?= $gateY + 8 ?>h16" class="gate-line"/>
                <use href="#gate-folder" x="38" y="<?= $gateY ?>" width="20" height="16" class="gate-folder"/>
                <text x="66" y="<?= $gateY + 13 ?>" class="gate-name"><?= $gateName ?></text>
                <use href="#gate-check" x="326" y="<?= $gateY ?>" width="16" height="16" class="gate-check"/>
                <?php endforeach; ?>

                <!-- the one that goes away -->
                <path d="M18 136h16" class="gate-line"/>
                <rect x="32" y="122" width="320" height="28" rx="4" class="gate-target-bg"/>
                <g class="gate-target">
                    <use href="#gate-folder" x="38" y="128" width="20" height="16" class="gate-folder-target"/>
                    <text x="66" y="141" class="gate-name gate-name-target">install</text>
                    <path d="M36 136h76" class="gate-strike"/>
                </g>
                <use href="#gate-trash" x="326" y="128" width="16" height="16" class="gate-trash"/>

                <path d="M18 164h16" class="gate-line"/>
                <use href="#gate-folder" x="38" y="156" width="20" height="16" class="gate-folder"/>
                <text x="66" y="169" class="gate-name">lib</text>
                <use href="#gate-check" x="326" y="156" width="16" height="16" class="gate-check"/>
            </svg>

            <?php if ($gateError): ?>
                <div class="alert alert-danger">
                    <strong><?= $e(__('The folder could not be removed automatically.')) ?></strong><br>
                    <?= $e($gateError) ?>
                </div>
                <p><?= $e(__('Remove it by hand, then press Check again:')) ?></p>
                <p class="mb-1"><strong>Linux / macOS</strong></p>
                <pre>rm -rf <?= $e(escapeshellarg($gatePath)) ?></pre>
                <p class="mb-1"><strong>Windows</strong></p>
                <pre>rmdir /s /q "<?= $e($gatePath) ?>"</pre>
                <p class="mb-1"><strong>cPanel / FTP</strong></p>
                <p><?= $e(__('Open File Manager, go to the SLiMS folder and delete the folder named install.')) ?></p>
                <form method="post" action="<?= $e($gateSelf) ?>" class="d-inline">
                    <input type="hidden" name="gateToken" value="<?= $e($_SESSION['installer_gate_token']) ?>">
                    <button type="submit" name="removeInstaller" value="1" class="btn btn-outline-secondary"><?= $e(__('Try to remove again')) ?></button>
                </form>
                <a href="<?= $e($gateSelf) ?>" class="btn btn-primary"><?= $e(__('Check again')) ?></a>
            <?php else: ?>
                <form method="post" action="<?= $e($gateSelf) ?>">
                    <input type="hidden" name="gateToken" value="<?= $e($_SESSION['installer_gate_token']) ?>">
                    <button type="submit" name="removeInstaller" value="1" class="btn btn-danger btn-lg btn-block"><?= $e(__('Remove the install folder now')) ?></button>
                </form>
            <?php endif; ?>

        </div>
    </div>
    <p class="text-center mt-3"><a href="<?= $e(AWB) ?>logout.php" class="text-muted"><?= $e(__('Logout')) ?></a></p>
</div>
</body>
</html>
<?php
exit;
