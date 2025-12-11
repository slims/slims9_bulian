<?php
/**
 * @author Heru Subekti
 * @email heroe.soebekti@gmail.com
 * @create date 2025-12-11 20:11:00
 * @modify date 2025-12-11 20:11:00
 * @license GPLv3
 * @desc Image Maintenance Tool Class: Provides logic for image file and database synchronization, cleanup, rebuild, and backup.
 */

class ImageMaintenanceTool
{
    private $dbs;
    private $can_write;
    const MEMBER_IMAGE_DIR = SB . 'images/persons/';
    const BIBLIO_IMAGE_DIR = SB . 'images/docs/';

    public function __construct($dbs, $can_write)
    {
        $this->dbs = $dbs;
        $this->can_write = $can_write;
    }

    private function _echo($message)
    {
        echo $message . "<br />\n";
        @ob_flush();
        @flush();
    }

    private function getPhysicalFiles($dir) {
        $files = []; $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'];
        $ignore_persons = ['non_member.png', 'person.png', 'photo.png', 'avatar.jpg'];

        if (is_dir($dir)) {
            $all_files = array_diff(scandir($dir), array('.', '..'));
            foreach ($all_files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (($dir === self::BIBLIO_IMAGE_DIR && strpos($file, 'thumb') !== false) ||
                    ($dir === self::MEMBER_IMAGE_DIR && in_array($file, $ignore_persons))) continue;

                if (is_file($dir . $file) && in_array($ext, $image_extensions)) {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }

    private function getDbImagesWithId() {
        $db_images = [];
        $queries = [
            'member_id' => ["SELECT member_id, member_image FROM member WHERE member_image IS NOT NULL AND member_image != ''", 'member_image'],
            'user_id' => ["SELECT user_id, user_image FROM user WHERE user_image IS NOT NULL AND user_image != ''", 'user_image'],
            'biblio_id' => ["SELECT biblio_id, image FROM biblio WHERE image IS NOT NULL AND image != ''", 'image']
        ];

        foreach ($queries as $key => list($sql, $image_field)) {
            $q = $this->dbs->query($sql);
            if ($q) {
                while ($r = $q->fetch_assoc()) {
                    $filename = trim($r[$image_field]);
                    if (!isset($db_images[$filename])) { $db_images[$filename] = ['member_id' => [], 'user_id' => [], 'biblio_id' => []]; }
                    $db_images[$filename][$key][] = $r[explode('_', $key)[0] . '_id'];
                }
            }
        }
        foreach ($db_images as $filename => &$source) {
            $source['member_id'] = array_unique($source['member_id']);
            $source['user_id'] = array_unique($source['user_id']);
            $source['biblio_id'] = array_unique($source['biblio_id']);
        }
        return $db_images;
    }

    private function getDbImagesBySource($source_type) {
        $db_images_map = $this->getDbImagesWithId();
        $db_filtered = [];

        foreach ($db_images_map as $image => $sources) {
            $is_persons = !empty($sources['member_id']) || !empty($sources['user_id']);
            $is_biblio = !empty($sources['biblio_id']);

            if (($source_type === 'persons' && $is_persons) || ($source_type === 'biblio' && $is_biblio)) {
                $db_filtered[] = $image;
            }
        }
        return array_unique($db_filtered);
    }

    private function rebuildImageGd($file_name, $dir) {
        $full_path = $dir . $file_name;
        if (!is_file($full_path) || !extension_loaded('gd') || ($info = @getimagesize($full_path)) === false) return false;

        list($width, $height, $image_type) = $info; $src = null;

        if ($image_type == IMAGETYPE_JPEG) { $src = @imagecreatefromjpeg($full_path); }
        elseif ($image_type == IMAGETYPE_PNG) { $src = @imagecreatefrompng($full_path); }
        elseif ($image_type == IMAGETYPE_GIF) { $src = @imagecreatefromgif($full_path); }
        else { return false; }

        if ($src === false) return false;

        $dst = @imagecreatetruecolor($width, $height);

        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($dst, false); imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $width, $height);

        $success = false;
        if ($image_type == IMAGETYPE_JPEG) { $success = @imagejpeg($dst, $full_path, 90); }
        elseif ($image_type == IMAGETYPE_PNG) { $success = @imagepng($dst, $full_path, 9); }
        elseif ($image_type == IMAGETYPE_GIF) { $success = @imagegif($dst, $full_path); }

        @imagedestroy($src); @imagedestroy($dst);

        return $success;
    }

    public function calculateStats($group) {
        $dir = $group === 'persons' ? self::MEMBER_IMAGE_DIR : self::BIBLIO_IMAGE_DIR;
        $db_images = $this->getDbImagesBySource($group);
        $physical_files = $this->getPhysicalFiles($dir);
        return [
            count($db_images),
            count($physical_files),
            count(array_diff($db_images, $physical_files)),
            count(array_diff($physical_files, $db_images)),
            $dir
        ];
    }

    public function runTask($task) {
        global $sysconf;
        
        $start = microtime(true);
        $target_group = end(explode('_', $task));

        $group = ($target_group === 'biblio') ? 'biblio' : 'persons';
        $dir = $group === 'persons' ? self::MEMBER_IMAGE_DIR : self::BIBLIO_IMAGE_DIR;
        $display_dir = $group === 'persons' ? 'images/persons/' : 'images/docs/';
        $db_table_name = $group === 'persons' ? 'Member/User' : 'Biblio';
        $db_fields = $group === 'persons' ? ['member' => 'member_image', 'user' => 'user_image'] : ['biblio' => 'image'];

        if (strpos($task, 'rebuild_all_images') !== false) {
            if (!$this->can_write || !extension_loaded('gd')) { $this->_echo('<span style="color:red">' . (!$this->can_write ? __('You are not authorized to perform this action') : __('ERROR: PHP GD extension is required.')) . '</span>'); return; }

            $files_to_process = [
                'Person' => [$this->getPhysicalFiles(self::MEMBER_IMAGE_DIR), self::MEMBER_IMAGE_DIR],
                'Biblio' => [$this->getPhysicalFiles(self::BIBLIO_IMAGE_DIR), self::BIBLIO_IMAGE_DIR]
            ];
            $all_files_count = count($files_to_process['Person'][0]) + count($files_to_process['Biblio'][0]);
            $processed_count = $failed_count = 0;

            $this->_echo(sprintf(__('Attempting to rebuild %d image files (All groups) using PHP GD...'), $all_files_count));
            echo '<div style="background-color: #000; color: #0f0; padding: 10px; margin-top: 10px; max-height: 250px; overflow-y: auto; font-size: 0.9em; line-height: 1.5;">'; @ob_flush(); @flush();

            foreach ($files_to_process as $type => list($files, $process_dir)) {
                foreach ($files as $file) {
                    echo 'Processing **' . $type . '**: <span style="font-weight: bold;">' . htmlspecialchars($file) . '</span>... ';
                    if ($this->rebuildImageGd($file, $process_dir)) { echo '<span style="color:#0f0;">' . __('Success.') . '</span><br>'; $processed_count++; }
                    else { echo '<span style="color:#ff0;">' . __('Failed. (Possibly unsupported format or GD error)') . '</span><br>'; $failed_count++; }
                    @ob_flush(); @flush();
                }
            }
            echo '</div>'; $this->_echo('<br>');
            $this->_echo('<span style="color:green">' . sprintf(__('Done. Successfully rebuilt %d files, failed %d files (All groups).'), $processed_count, $failed_count) . '</span>');
            if ($processed_count > 0) { writeLog('staff', $_SESSION['uid'], 'Maintenance', "Rebuilt $processed_count image files (All groups) using PHP-GD", 'Maintenance', 'Update'); }
            return;
        }

        if (strpos($task, 'backup_images') !== false) {
            if (!$this->can_write || !class_exists('ZipArchive')) { $this->_echo('<span style="color:red">' . (!$this->can_write ? __('You are not authorized to perform this action') : __('ERROR: PHP ZipArchive extension is required.')) . '</span>'); return; }
            $start_backup = microtime(true);
            $backup_filename = 'all_images_backup_' . date('Ymd_His') . '.zip';
            $backup_dir = rtrim($sysconf['backup_dir'] ?? dirname(__FILE__), '/');
            $output_path = $backup_dir . '/' . $backup_filename;
            $zip = new ZipArchive();
            if ($zip->open($output_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) { $this->_echo('<span style="color:red">' . __('ERROR: Could not create zip file at ') . $output_path . ' (' . __('Check permissions') . ').</span>'); return; }

            $file_count = 0;
            $directories = ['persons' => self::MEMBER_IMAGE_DIR, 'biblio' => self::BIBLIO_IMAGE_DIR];

            foreach ($directories as $zip_dir => $source_dir) {
                $files_to_backup = array_diff(scandir($source_dir), array('.', '..'));
                foreach ($files_to_backup as $file) {
                    $full_path = $source_dir . $file;
                    if (is_file($full_path)) { $zip->addFile($full_path, $zip_dir . '/' . $file); $file_count++; }
                }
            }
            $this->_echo(sprintf(__('Starting backup of %d image files (All groups)...'), $file_count));

            if ($zip->close()) {
                writeLog('staff', $_SESSION['uid'], 'Maintenance', "Created image backup: $backup_filename (Files: $file_count)", 'Maintenance', 'Create');
                $filesize_mb = round(filesize($output_path) / (1024 * 1024), 2);
                $this->_echo(str_repeat('=', 60));
                $this->_echo('<span style="color:green; font-weight: bold;">' . __('Backup Completed Successfully (All groups)!') . '</span>');
                $this->_echo(sprintf(__('Total files backed up: %d'), $file_count));
                $this->_echo(sprintf(__('Backup file size: %.2f MB'), $filesize_mb));
                $this->_echo(sprintf(__('Time taken: %.2f second(s)'), microtime(true) - $start_backup));
                $this->_echo(str_repeat('=', 60));

                $download_url = MWB . 'system/image_maintenance.php?action=download&file=' . urlencode($backup_filename);

                $this->_echo(__('Click the button below to download the backup file:') . ':');
                $this->_echo('<a href="' . $download_url . '" target="_top" class="btn btn-primary" style="padding: 10px 20px; text-decoration: none; margin-top: 10px;">' . __('Download Backup File') . '</a>');
                $this->_echo('<br><small style="color:#aaa;">* ' . __('The download link only works after this process is complete. The file will be automatically deleted after download.') . '</small>');
            } else {
                $this->_echo('<span style="color:red">' . __('ERROR: Failed to finalize zip file.') . '</span>');
            }
            return;
        }

        $this->_echo('<strong>' . $db_table_name . ' ' . __('Image Maintenance') . '</strong>');
        $this->_echo(__('Image Directory') . ': ' . $display_dir);
        $this->_echo(date('Y-m-d H:i:s'));
        $this->_echo(str_repeat('-', 60));

        $db_images = $this->getDbImagesBySource($group);
        $physical_files = $this->getPhysicalFiles($dir);

        switch ($task) {
            case 'stats_persons':
            case 'stats_biblio':
                list($db_total, $file_total, $orphaned, $ghosts, $current_dir) = $this->calculateStats($group);
                $this->_echo(__('Target Group') . ': **' . $db_table_name . '**');
                $this->_echo(__('Total unique images in DB') . ': ' . number_format($db_total));
                $this->_echo(__('Total physical image files (Excluding defaults/thumbs)') . ': ' . number_format($file_total));
                $this->_echo('<span style="color:red">' . __('Orphaned Images (DB entry, no file)') . ': ' . number_format($orphaned) . '</span>');
                $this->_echo('<span style="color:red">' . __('Ghost Files (File exists, no DB entry, Excl. defaults/thumbs)') . ': ' . number_format($ghosts) . '</span>');
                break;

            case 'find_orphaned_persons':
            case 'find_orphaned_biblio':
                $db_images_map = $this->getDbImagesWithId();
                $orphans = array_diff($db_images, $physical_files);
                $this->_echo(sprintf(__('Searching for Orphaned Images (DB entry but no physical file)... Found %d orphaned image entries.'), count($orphans)));
                if (count($orphans) > 0) {
                    $this->_echo(__('List (Image Name -> Source [IDs])') . ':');
                    $this->_echo('<ul style="margin: 0; list-style-type: none;">');
                    foreach ($orphans as $image) {
                        $sources = []; $ids = $db_images_map[$image] ?? ['member_id' => [], 'user_id' => [], 'biblio_id' => []];
                        if ($group === 'persons') {
                            if (!empty($ids['member_id'])) { $sources[] = 'Member [' . implode(', ', $ids['member_id']) . ']'; }
                            if (!empty($ids['user_id'])) { $sources[] = 'User [' . implode(', ', $ids['user_id']) . ']'; }
                        } else {
                            if (!empty($ids['biblio_id'])) { $sources[] = 'Biblio [' . implode(', ', $ids['biblio_id']) . ']'; }
                        }
                        $this->_echo('<li>- **' . htmlspecialchars($image) . '** (' . implode('; ', $sources) . ')</li>');
                    }
                    $this->_echo('</ul><span style="color:red">' . __('WARNING: These DB entries can be cleaned up using the "Cleanup Orphaned DB" task.') . '</span>');
                }
                break;

            case 'cleanup_orphaned_persons':
            case 'cleanup_orphaned_biblio':
                if (!$this->can_write) { $this->_echo('<span style="color:red">' . __('You are not authorized to perform this action') . '</span>'); break; }
                $orphans_sql = array_map([$this->dbs, 'escape_string'], array_diff($db_images, $physical_files));
                if (empty($orphans_sql)) { $this->_echo('<span style="color:green">' . __('No orphaned image entries found to cleanup.') . '</span>'); break; }

                $image_list = "'" . implode("','", $orphans_sql) . "'"; $affected = 0;
                $this->_echo(sprintf(__('Attempting to set image column to NULL for %d unique entries in %s...'), count($orphans_sql), $db_table_name));

                foreach ($db_fields as $table => $field) {
                    $this->dbs->query("UPDATE $table SET $field = NULL WHERE $field IN ($image_list)");
                    $rows = (int)$this->dbs->affected_rows; $affected += $rows;
                    $msg = $this->dbs->error
                        ? '<span style="color:red">' . sprintf(__('Failed to cleanup %s DB entries'), $table) . ' : ' . $this->dbs->error . '</span>'
                        : '<span style="color:green">' . sprintf(__('Successfully updated %d %s database entries.'), $rows, $table) . '</span>';
                    $this->_echo($msg);
                }
                if ($affected > 0) { writeLog('staff', $_SESSION['uid'], 'Maintenance', "Cleaned up $affected orphaned $db_table_name image DB entries", 'Maintenance', 'Update'); }
                else { $this->_echo('<span style="color:red">' . __('No database entries were updated (check tables or permissions).') . '</span>'); }
                break;

            case 'find_ghosts_persons':
            case 'find_ghosts_biblio':
                $ghosts = array_diff($physical_files, $db_images);
                $this->_echo(sprintf(__('Searching for Ghost Files (Physical image file but no DB entry)... Found %d ghost image files (Excluding defaults/thumbs).'), count($ghosts)));

                if (count($ghosts) > 0) {
                    $images_per_row = 8; $thumb_size = '80px';
                    $image_base_url = $group === 'persons' ? '../../../images/persons/' : '../../../images/docs/';
                    $check_all_id = 'checkAllGhosts_' . $group; $delete_btn_id = 'deleteSelectedGhostsBtn_' . $group; $checkbox_class = 'ghost-checkbox_' . $group;

                    $this->_echo('<strong>' . __('List of Ghost Files (Physical files not linked in DB)') . ':</strong>');
                    $this->_echo('<div class="d-flex justify-content-between align-items-center py-2">
                                <div><input type="checkbox" id="' . $check_all_id . '"> <label for="' . $check_all_id . '">' . __('Select All') . '</label></div>
                                <button id="' . $delete_btn_id . '" class="btn btn-warning" disabled onclick="deleteSelectedGhosts()">' . __('Delete Selected') . '</button>
                            </div>');

                    echo '<form id="ghostsForm_' . $group . '" method="POST" action="">';
                    echo '<div class="d-flex flex-wrap align-items-start py-2">';

                    foreach ($ghosts as $file) {
                        $file_encoded = htmlspecialchars(urlencode($file));
                        echo '<div class="d-flex flex-column align-items-center text-center border p-1 m-1 position-relative" style="flex: 0 0 calc(100%/' . $images_per_row . ' - 10px);">
                                <input type="checkbox" name="selected_ghosts[]" value="' . htmlspecialchars($file) . '" class="' . $checkbox_class . '">
                                <img src="' . $image_base_url . $file_encoded . '" alt="' . htmlspecialchars($file) . '" loading="lazy" class="border" style="width: ' . $thumb_size . '; height: ' . $thumb_size . '; object-fit: cover;">
                                <small class="d-block mt-1 mx-auto" title="' . htmlspecialchars($file) . '" style="max-width: ' . $thumb_size . '; font-size: 0.7em; word-break: break-all;">' . htmlspecialchars(substr($file, 0, 15)) . '...</small>
                                <button type="button" class="btn btn-danger btn-sm mt-1 p-1" style="font-size: 0.7em;" onclick="deleteSingleGhost(\'' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '\')">' . __('Delete') . '</button>
                            </div>';
                    }
                    echo '</div></form>';
                    $this->_echo('<span style="color:red">' . __('WARNING: These files can be safely deleted using the "Cleanup ALL Ghost Files" task or individually/batch selected.') . '</span>');

                    echo '<script>
                        const group = "' . $group . '", checkAll = document.getElementById("' . $check_all_id . '"),
                              checkboxes = document.querySelectorAll(".' . $checkbox_class . '"),
                              deleteBtn = document.getElementById("' . $delete_btn_id . '");

                        checkAll.addEventListener("change", function() { checkboxes.forEach(c => c.checked = this.checked); toggleDeleteButton(); });
                        checkboxes.forEach(c => c.addEventListener("change", toggleDeleteButton));

                        function toggleDeleteButton() {
                            deleteBtn.disabled = !Array.from(checkboxes).some(c => c.checked);
                        }

                        function deleteGhosts(files, isSingle) {
                            if (!files.length) { if (!isSingle) alert("' . __('No files selected.') . '"); return; }
                            const confirmMsg = isSingle
                                ? "' . sprintf(__('Are you sure you want to delete the file %s?'), '" + files[0] + "') . '"
                                : "' . __('Are you sure you want to delete the selected ghost files?') . '";

                            if (confirm(confirmMsg)) {
                                const form = document.createElement("form");
                                form.method = "POST";
                                form.action = "' . MWB . 'system/image_maintenance.php?action=run&task=delete_selected_ghosts_" + group;
                                form.target = "progress";
                                files.forEach(file => {
                                    const input = document.createElement("input");
                                    input.type = "hidden"; input.name = "selected_ghosts[]"; input.value = file;
                                    form.appendChild(input);
                                });
                                document.body.appendChild(form); form.submit(); form.remove();
                            }
                        }

                        window.deleteSelectedGhosts = () => deleteGhosts(Array.from(checkboxes).filter(c => c.checked).map(c => c.value), false);
                        window.deleteSingleGhost = (f) => deleteGhosts([f], true);

                        toggleDeleteButton();
                    </script>';
                }
                break;

            case 'delete_selected_ghosts_persons':
            case 'cleanup_ghosts_persons':
            case 'delete_selected_ghosts_biblio':
            case 'cleanup_ghosts_biblio':
                if (!$this->can_write) { $this->_echo('<span style="color:red">' . __('You are not authorized to perform this action') . '</span>'); break; }

                $is_selective = strpos($task, 'delete_selected') !== false;
                $files_to_delete = $is_selective ? ($_POST['selected_ghosts'] ?? []) : array_diff($physical_files, $db_images);

                if (empty($files_to_delete)) {
                    $this->_echo('<span style="color:orange">' . ($is_selective ? __('No ghost files selected for deletion.') : __('No ghost files found to cleanup.')) . '</span>');
                    break;
                }

                $deleted_count = 0;
                $this->_echo(sprintf(__('Attempting to delete %d %s ghost files in %s...'), count($files_to_delete), ($is_selective ? 'selected' : 'ALL'), $dir));

                $db_images_all = array_merge($this->getDbImagesBySource('persons'), $this->getDbImagesBySource('biblio'));
                $safe_to_delete = array_diff($physical_files, $db_images_all);

                foreach ($files_to_delete as $file) {
                    $full_path = $dir . $file;
                    if (in_array($file, $safe_to_delete) && is_file($full_path)) {
                        if (unlink($full_path)) {
                            $this->_echo('- Deleted: ' . htmlspecialchars($file));
                            $deleted_count++;
                        } else {
                            $this->_echo('<span style="color:orange">- ' . __('Failed to delete') . ': ' . htmlspecialchars($file) . ' (check file permissions)</span>');
                        }
                    } else if (!$is_selective) {
                             if (is_file($full_path) && unlink($full_path)) {
                                 $this->_echo('- Deleted: ' . htmlspecialchars($file));
                                 $deleted_count++;
                             } else {
                                 $this->_echo('<span style="color:orange">- ' . __('Failed to delete') . ': ' . htmlspecialchars($file) . ' (check file permissions)</span>');
                             }
                    } else {
                        $this->_echo('<span style="color:gray">- ' . __('Skipped') . ': ' . htmlspecialchars($file) . ' (' . __('File is either used in DB, a default file, or already removed') . ')</span>');
                    }
                }

                if ($deleted_count > 0) { writeLog('staff', $_SESSION['uid'], 'Maintenance', "Cleaned up $deleted_count " . ($is_selective ? 'selected' : 'ALL') . " ghost images ($db_table_name)", 'Maintenance', 'Delete'); }
                $this->_echo('<span style="color:green">' . sprintf(__('Done. Successfully deleted %d files.'), $deleted_count) . '</span>');

                break;
            default:
                $this->_echo(__('Invalid or unhandled task specified.'));
                break;
        }

        $elapsed = microtime(true) - $start;
        $this->_echo(str_repeat('-', 60));
        $this->_echo(sprintf(__('Finished in %.2f second(s)'), $elapsed));
    }

    public function handleDownload() {
        global $sysconf;

        if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
            $filename = basename($_GET['file']);
            $backup_dir = rtrim($sysconf['backup_dir'] ?? dirname(__FILE__), '/');
            $filepath = $backup_dir . '/' . $filename;
            if (strpos($filename, 'all_images_backup_') === 0 && substr($filename, -4) === '.zip' && is_file($filepath)) {
                if (ob_get_level()) { ob_end_clean(); }
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                @unlink($filepath);
                writeLog('staff', $_SESSION['uid'], 'Maintenance', "Downloaded and deleted image backup: $filename", 'Maintenance', 'Download');
                exit;
            } else {
                die(__('File not found or invalid filename.'));
            }
        }
    }
}