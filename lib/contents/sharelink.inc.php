<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-21 22:08:09
 * @modify date 2022-12-22 12:59:22
 * @license GPLv3
 * @desc [description]
 */
$socialMedia = [
	'WhatsApp' => ['color' => '#25d366', 'link' => ''],
	'Telegram' => ['color' => '#0088cc', 'link' => ''],
	'Link' => ['color' => '#828282', 'link' => '']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $page_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'css/bootstrap.min.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'css/printed.css?v='.date('this'); ?>" />
	<script src="<?= JWB . 'jquery.js'?>"></script>
	<style>
		<?php foreach($socialMedia as $media => $detail): ?>
		<?php extract($detail) ?>
		.share-to-<?= strtolower(str_replace('-','', $media)) ?> {
			color: <?= $color ?>;
			border-radius: 100%;
			padding: 5px;
			width: 62px;
		}

		.label-<?= strtolower(str_replace('-','', $media)) ?>:hover {
			color: <?= $color ?>;
		}

		.share-to-<?= strtolower(str_replace('-','', $media)) ?>:hover {
			color: <?= $color ?>;
			border-radius: 100%;
			border: 1px solid <?= $color ?>;
		}

		.hover-<?= strtolower(str_replace('-','', $media)) ?>:hover {
			color: <?= $color ?>;
			cursor: pointer;
		}
		<?php endforeach; ?>
	</style>
</head>
<body>
	<div id="pageContent">
		<div class="container-fluid">
			<div class="row">
				<?php foreach($socialMedia as $media => $detail): ?>
					<div data-id="<?= (int)$_GET['id'] ?>" data-type="<?= strtolower(str_replace('-','', $media)) ?>" class="share-to-click col-4 text-center hover-<?= strtolower(str_replace('-','', $media)) ?>">
						<div class="share-to-<?= strtolower(str_replace('-','', $media)) ?> d-block mx-auto">
							<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi d-block mx-auto m-1" viewBox="0 0 16 16">
								<?php 
								switch ($media) {
									case 'WhatsApp':
										echo '<path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>';
										break;
									
									case 'Telegram':
										echo '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09z"/>';
										break;

									case 'Twitter':
										echo '<path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>';
										break;
										
									case 'Facebook':
										echo '<path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>';
										break;
									
									case 'E-Mail':
										echo '<path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2H2Zm3.708 6.208L1 11.105V5.383l4.708 2.825ZM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2-7-4.2Z"/>
										<path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648Zm-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>';
										break;
										
									default:
										echo '<path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/>
										<path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/>
									</svg>';
										break;
								}
								?>
							</svg>
						</div>
						<label><?= $media ?></label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<script>
		$(document).ready(function(){
			$('.share-to-click').click(function(){
				let id = $(this).data('id')
				let type = $(this).data('type')
				
				<?php $link = \SLiMS\Url::getSlimsBaseUri('?p=show_detail&id='); ?>
				<?php $title = simbio_security::xssFree($_GET['title']); ?>
				switch (type) {
					case 'whatsapp':
						<?php $waUrl = utility::isMobileBrowser() ? 'https://api.whatsapp.com/send?text=' : 'https://web.whatsapp.com/send?text='; ?>
						parent.window.location.href = '<?= $waUrl . $link->encode()  ?>' + id;
						break;
				
					case 'telegram':
						parent.window.location.href = 'https://telegram.me/share/url?url=<?= $link->encode() ?>' + id + '&text=<?= $title ?>'
						break;
						
					default:
					navigator.clipboard.writeText(`<?= $link ?>${id}`)
						.then(() => {
							parent.toastr.info('<?= __('Link copied to clipboard') ?>', '<?= $title ?>',{positionClass: 'toast-bottom-center'});
						})
						.catch(err => {
							alert('Error in copying text: ', err);
						});
						parent.$("#mediaSocialModal").modal('hide');
						break;
				}
			})
		})
	</script>
</body>
</html>
<?php
exit;