<?php
/**
 * Copyright (C) 2007,Hendro Wicaksono (hendrowicaksono@gmail.com)
 * Modification by Arie Nugraha (dicarve@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// get file ID
$fileID = isset($_GET['fid'])?(integer)$_GET['fid']:0;
// get biblioID
$biblioID = isset($_GET['bid'])?(integer)$_GET['bid']:0;
// get file data
// query file to database
$sql_q = 'SELECT att.*, f.* FROM biblio_attachment AS att
    LEFT JOIN files AS f ON att.file_id=f.file_id
    WHERE att.file_id='.$fileID.' AND att.biblio_id='.$biblioID.' AND att.access_type=\'public\'';
$file_q = $dbs->query($sql_q);
// check if file exists
if ($file_q->num_rows < 1) {
    die();
}
// check if file exists
$file_d = $file_q->fetch_assoc();
$file_loc = REPOBS.str_ireplace('/', DS, $file_d['file_dir']).DS.$file_d['file_name'];
if (!file_exists($file_loc)) {
  die();
}
// multimedia URL
$multimedia_url = SWB.'/index.php?p=fstream-pdf&fid='.$fileID.'&bid='.$biblioID;
$multimedia_url = urlencode($multimedia_url);

// flowplayer settings
$splash = SWB.IMG.'/senayan-player-splash.png';
$flowplayer_core = JWB.'flowplayer/flowplayer-3.1.0.swf';
$flowplayer_api = JWB.'flowplayer/flowplayer-3.1.0.min.js';
$flowplayer_audio_plugin = JWB.'flowplayer/flowplayer.audio-3.1.0.swf';
?>
<!DOCTYPE html>
<html>
<head>
<!-- by Hendro Wicaksono -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script type="text/javascript" src="<?php echo $flowplayer_api; ?>"></script>
<title>SLiMS Multimedia Viewer</title>
</head>
<body style="padding: 0; margin: 0;">
<a href="#" style="display: block; width: 640px; height: 480px;" id="player"></a>
<script type="text/javascript">
flowplayer("player", "<?php echo $flowplayer_core; ?>", {
    plugins: { audio: { url: '<?php echo $flowplayer_audio_plugin; ?>' } },
    playlist: [
        {
            url: '<?php echo $multimedia_url; ?>',
            onFinish: function() {
                this.unload();
            }
        }
    ]
});
</script>
</body>
</html>
<?php exit(); ?>
