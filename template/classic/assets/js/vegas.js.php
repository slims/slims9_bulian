<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-29 23:33
 * @File name           : vegas.js.php
 */

?>

<script>
  $('.c-header, .vegas-slide').vegas({
        delay: <?= $sysconf['template']['classic_slide_delay']; ?>,
        timer: false,
        transition: '<?= $sysconf['template']['classic_slide_transition']; ?>',
        animation: '<?= $sysconf['template']['classic_slide_animation']; ?>',
        slides: [
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide1.jpg" },
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide2.jpg" },
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide3.jpg" },
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide4.jpg" }
        ]
    });
</script>
