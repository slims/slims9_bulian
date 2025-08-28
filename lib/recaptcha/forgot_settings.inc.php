<?php 

$sysconf['captcha']['forgot']['folder'] = 'recaptcha'; // folder name inside the SENAYAN_LIB_DIR folder
$sysconf['captcha']['forgot']['incfile'] = 'recaptchalib-v2.php'; // php file that needs to be included in php file
$sysconf['captcha']['forgot']['webfile'] = ''; // php file that needs to accessed to create captcha image
$sysconf['captcha']['forgot']['publickey'] = '6LdCzFAUAAAAAKV0pEX3h3523MZA5ATRZf2GpgQC'; // some captcha providers need this. Ajdust it with yours
$sysconf['captcha']['forgot']['privatekey'] = '6LdCzFAUAAAAABb8kVMaf97GiQFP9lfX56BPhhGs'; // some captcha providers need this. Ajdust it with yours

$sysconf['captcha']['forgot']['recaptcha']['theme'] = 'white'; // Possible values: 'red' | 'white' | 'blackglass' | 'clean' | 'custom'
$sysconf['captcha']['forgot']['recaptcha']['lang'] = 'en'; // Possible values: 'en' (english) | 'nl' (Dutch) | 'fr' (French) | 'de' (German) | 'pt' (Portuguese) | 'ru' (Russian) | 'es' (Spanish) | 'tr' (Turkish)
$sysconf['captcha']['forgot']['recaptcha']['customlang']['enable'] = false;
$sysconf['captcha']['forgot']['recaptcha']['customlang']['instructions_visual'] = 'Ketik dua kata diatas:';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['instructions_audio'] = 'Ketik kata yang anda dengar:';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['play_again'] = 'Putar suara kembali';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['cant_hear_this'] = 'Unduh suara format MP3';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['visual_challenge'] = 'Captcha visual';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['audio_challenge'] = 'Captcha audio';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['refresh_btn'] = 'Minta kata baru';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['help_btn'] = 'Bantuan';
$sysconf['captcha']['forgot']['recaptcha']['customlang']['incorrect_try_again'] = 'Salah. Coba lagi.';

?>