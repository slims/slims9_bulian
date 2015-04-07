<?php 

$sysconf['captcha']['smc']['folder'] = 'recaptcha'; // folder name inside the SENAYAN_LIB_DIR folder
$sysconf['captcha']['smc']['incfile'] = 'recaptchalib.php'; // php file that needs to be included in php file
$sysconf['captcha']['smc']['webfile'] = ''; // php file that needs to accessed to create captcha image
$sysconf['captcha']['smc']['publickey'] = '6Ld41r4SAAAAAFjuQln14H5il6sARyliKWu8oB_8'; // some captcha providers need this. Ajdust it with yours
$sysconf['captcha']['smc']['privatekey'] = '6Ld41r4SAAAAAB-u-GiQRskOU3POAH20Vuy6Ytgt'; // some captcha providers need this. Ajdust it with yours

$sysconf['captcha']['smc']['recaptcha']['theme'] = 'clean'; // Possible values: 'red' | 'white' | 'blackglass' | 'clean' | 'custom'
$sysconf['captcha']['smc']['recaptcha']['lang'] = 'en'; // Possible values: 'en' (english) | 'nl' (Dutch) | 'fr' (French) | 'de' (German) | 'pt' (Portuguese) | 'ru' (Russian) | 'es' (Spanish) | 'tr' (Turkish)
$sysconf['captcha']['smc']['recaptcha']['customlang']['enable'] = false;
$sysconf['captcha']['smc']['recaptcha']['customlang']['instructions_visual'] = 'Ketik dua kata diatas:';
$sysconf['captcha']['smc']['recaptcha']['customlang']['instructions_audio'] = 'Ketik kata yang anda dengar:';
$sysconf['captcha']['smc']['recaptcha']['customlang']['play_again'] = 'Putar suara kembali';
$sysconf['captcha']['smc']['recaptcha']['customlang']['cant_hear_this'] = 'Unduh suara format MP3';
$sysconf['captcha']['smc']['recaptcha']['customlang']['visual_challenge'] = 'Captcha visual';
$sysconf['captcha']['smc']['recaptcha']['customlang']['audio_challenge'] = 'Captcha audio';
$sysconf['captcha']['smc']['recaptcha']['customlang']['refresh_btn'] = 'Minta kata baru';
$sysconf['captcha']['smc']['recaptcha']['customlang']['help_btn'] = 'Bantuan';
$sysconf['captcha']['smc']['recaptcha']['customlang']['incorrect_try_again'] = 'Salah. Coba lagi.';

?>