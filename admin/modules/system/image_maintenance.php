<?php
/**
 * @author Heru Subekti
 * @email heroe.soebekti@gmail.com
 * @create date 2025-12-11 20:10:00
 * @modify date 2025-12-11 20:10:00
 * @license GPLv3
 * @desc Image Maintenance Utility: Tool for auditing and synchronizing image file links between the database (Member/Biblio) and the filesystem.
 */

/* Image Maintenance section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require_once LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require 'ImageMaintenanceTool.inc.php';

$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!$can_read || ($_SESSION['uid'] != 1 && !utility::haveAccess('system.image-maintenance'))) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

$tool = new ImageMaintenanceTool($dbs, $can_write); 
$tool->handleDownload();

if (isset($_GET['action']) && $_GET['action'] === 'run') {
    @set_time_limit(0); @ob_implicit_flush(true);
    echo '<div style="font-family: monospace; padding: 10px;">';
    $tool->runTask($_GET['task'] ?? '');
    echo '</div>';
    exit;
}

$stats_persons = $tool->calculateStats('persons');
$stats_biblio = $tool->calculateStats('biblio');

list($db_total_persons, $file_total_persons, $orphaned_persons, $ghosts_persons, ) = $stats_persons;
list($db_total_biblio, $file_total_biblio, $orphaned_biblio, $ghosts_biblio, ) = $stats_biblio;

$db_total = $db_total_persons + $db_total_biblio;
$file_total = $file_total_persons + $file_total_biblio;
$orphaned = $orphaned_persons + $orphaned_biblio;
$ghosts = $ghosts_persons + $ghosts_biblio;

?>

<div class="menuBox">
    <div class="menuBoxInner circulationIcon">
        <div class="per_title">
            <h2><?= __('Image Maintenance'); ?></h2>
        </div>

        <div class="sub_section mb-2">
            <select class="form-control" id="maintenanceAction" onchange="runMaintenanceAction(this.value);">
                <option value="" selected disabled><?= __('Select Maintenance Action...'); ?></option>

                <optgroup label="<?= __('1. Member and User Images (images/persons/)'); ?>">
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=stats_persons"><?= __('Refresh Stats (Persons)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=find_orphaned_persons"><?= __('Find Orphaned DB Entries (Persons)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=find_ghosts_persons"><?= __('Find Ghost Files (Persons)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=cleanup_orphaned_persons" data-confirm="<?= __('This will set the member_image/user_image columns to NULL for all orphaned entries. Continue?'); ?>"><?= __('Cleanup Orphaned DB (Persons)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=cleanup_ghosts_persons" data-confirm="<?= __('This will delete ALL ghost files from the persons directory, excluding default files (non_member.png, person.png, photo.png). Use with caution! Continue?'); ?>"><?= __('Cleanup ALL Ghost Files (Persons)'); ?></option>
                </optgroup>

                <optgroup label="<?= __('2. Book Cover Images (images/docs/)'); ?>">
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=stats_biblio"><?= __('Refresh Stats (Biblio)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=find_orphaned_biblio"><?= __('Find Orphaned DB Entries (Biblio)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=find_ghosts_biblio"><?= __('Find Ghost Files (Biblio)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=cleanup_orphaned_biblio" data-confirm="<?= __('This will set the image column to NULL for all orphaned biblio entries. Continue?'); ?>"><?= __('Cleanup Orphaned DB (Biblio)'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=cleanup_ghosts_biblio" data-confirm="<?= __('This will delete ALL ghost files from the docs directory, excluding thumbnail files. Use with caution! Continue?'); ?>"><?= __('Cleanup ALL Ghost Files (Biblio)'); ?></option>
                </optgroup>

                <optgroup label="<?= __('3. Global Utility & Repair (All Groups)'); ?>">
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=rebuild_all_images_all" data-confirm="<?= __('This will rebuild ALL image files (Persons & Biblio) using GD to clean metadata. This may take a long time and requires PHP-GD extension. Continue?'); ?>"><?= __('Rebuild All Images (GD) - Clean Tampering'); ?></option>
                    <option value="<?= MWB . 'system/image_maintenance.php' ?>?action=run&amp;task=backup_images_all" data-confirm="<?= __('This will create a single ZIP backup of ALL images (Persons & Biblio), including default files and thumbnails. The file will be available for download after processing. Continue?'); ?>"><?= __('Backup All Images (ZIP)'); ?></option>
                </optgroup>

            </select>
        </div>
        <div class="infoBox">
            <?= __('This maintenance is used to find and clean up image entries that are inconsistent between the database (member, user, biblio tables) and the file system.'); ?>
            <div class="mt-2">
                <strong><?= __('Current Total Stats'); ?>:</strong>
                <span class="ml-2"><?= __('DB Images'); ?>: <strong><?= number_format($db_total); ?></strong></span>
                <span class="ml-2"><?= __('Physical Image Files (Excl. defaults/thumbs)'); ?>: <strong><?= number_format($file_total); ?></strong></span>
                <span class="ml-2" style="color:red"><?= __('Orphaned'); ?>: <strong><?= number_format($orphaned); ?></strong></span>
                <span class="ml-2" style="color:red"><?= __('Ghosts'); ?>: <strong><?= number_format($ghosts); ?></strong></span>
            </div>
            <div class="mt-2" style="font-size: small; color: #6c757d;">
                <?= __('Persons Directory'); ?>: <code>images/persons/</code><br>
                <?= __('Biblio Directory'); ?>: <code>images/docs/</code>
                <?php if (!extension_loaded('gd')): ?>
                    <span style="color:red; display:block; margin-top: 5px;"><?= __('WARNING: PHP GD extension is NOT loaded. Image Rebuilding feature will not work.'); ?></span>
                <?php endif; ?>
                <?php if (!class_exists('ZipArchive')): ?>
                    <span style="color:red; display:block; margin-top: 5px;"><?= __('WARNING: PHP ZipArchive extension is NOT loaded. Image Backup feature will not work.'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<iframe name="progress" id="progress" class="w-100" style="height: 320px; border: 1px solid #ddd;" frameborder="0"></iframe>

<script>
function runMaintenanceAction(url) {
    if (!url) return;

    const select = document.getElementById('maintenanceAction');
    const selectedOption = select.options[select.selectedIndex];
    const confirmMessage = selectedOption.getAttribute('data-confirm');

    setTimeout(() => { select.value = ""; }, 100);

    if (confirmMessage) {
        if (!confirm(confirmMessage)) {
            return;
        }
    }

    const progressFrame = window.frames['progress'];
    if (progressFrame) {
        progressFrame.location.href = url;
    } else {
        window.open(url, 'progress');
    }
}
</script>