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

/* Modified by Eddy Subratha 
 * -------------------------
 * Replaced Flash Player by HTML5 Player only. 
 * As we know, Flash Player need more resources
 * and also it's not supported by Adobe anymore. 
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
$mime = $file_d['mime_type'];
if (!file_exists($file_loc)) {
  die();
}
// multimedia URL
$multimedia_url = SWB.'index.php?p=fstream&fid='.$fileID.'&bid='.$biblioID;
// $multimedia_url = urlencode($multimedia_url);

// flowplayer settings
$cover = SWB.IMG.'/slims-splash.png';
$plyr_core = JWB.'plyr/';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo SWB.'css/bootstrap.min.css'?>">
<link rel="stylesheet" href="<?php echo $plyr_core ?>plyr.css">
<title>Multimedia Viewer</title>
</head>
<body>
    <div class="container">
            <div class="text-align-center align-middle">
            <?php if($mime == 'audio/mpeg') : ?>
            <audio id="player" controls class="col" >
                <source src="<?php echo $multimedia_url ?>" type="<?php echo $mime ?>">
            </audio>
            <?php else : ?>
            <div class="row">
            <video controls crossorigin playsinline poster="<?php echo $cover ?>" id="player">
                <source src="<?php echo $multimedia_url ?>" type="<?php echo $mime ?>" >
            </video>
            </div>
            <?php endif ?>
            </div>  
    </div>
</body>
<script src="<?php echo $plyr_core ?>plyr.polyfilled.js" crossorigin="anonymous"></script>
<script>const player = new Plyr('#player',{
loadSprite: true,
iconUrl: '<?php echo $plyr_core ?>plyr.svg',
blankVideo: '<?php echo $plyr_core ?>blank.mp4',
autoplay: true
});
</script>
</html>
<?php exit(); ?>
