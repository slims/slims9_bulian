<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 10/09/20 21.43
 * @File name           : Image.php
 */

trait Image
{
    function getImagePath($image, $path = 'docs')
    {
        // cover images var
        $thumb_url = '';
        $image = urlencode($image??'');
        $images_loc = 'images/' . $path . '/' . $image;
        $img_status = pathinfo('images/' . $path . '/' . $image);
        if(isset($img_status['extension'])){
            $thumb_url = './lib/minigalnano/createthumb.php?filename=' . urlencode($images_loc) . '&width=120';
        }else{
            $thumb_url = './lib/minigalnano/createthumb.php?filename=images/default/image.png&width=120';
        }

        return $thumb_url;
    }
}