<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-29 23:33
 * @File name           : vegas.js.php
 */

?>

<script>
  $('.c-header, .vegas-slide').vegas({
        delay: 15000,
        timer: false,
        animation: 'random',
        slides: [
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide1.jpg" },
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide2.jpg" },
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide3.jpg" },
            { src: "<?php echo CURRENT_TEMPLATE_DIR; ?>assets/images/slide4.jpg" }
        ]
    });
</script>
