<?php

/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-30 00:58
 * @File name           : detail_template.php
 */

use SLiMS\DB;

$dbs = DB::getInstance('mysqli');
?>

<div class="container">
    <div class="flex flex-wrap">
        <div class="w-64 mb-2">
            <div class="bg-grey-light p-12 rounded">
                <div class="shadow">
                    <?= $image; ?>
                </div>
            </div>
        </div>
        <div class="flex-1 p-0 px-md-4">
            <p class="lead"><i class="fas fa-bookmark text-green"></i> <?= $gmd_name; ?></p>
            <blockquote class="blockquote">
                <h4 class="mb-2"><?= $title; ?></h4>
                <footer class="blockquote-footer"><?= str_replace("<br />", '; ', $authors); ?></footer>
            </blockquote>
            <hr>
            <p class="text-grey-darker">
                <?= $notes ? $notes : '<i>' . __('Description Not Available') . '</i>'; ?>
            </p>
            <hr>

            <h5 class="mt-4 mb-1"><?= __('Availability'); ?></h5>
            <?= ($availability) ? $availability : '<p class="text-grey-dark">' . __('No copy data') . '</p>'; ?>

            <h5 class="mt-4 mb-1"><?= __('Detail Information'); ?></h5>
            <!-- START -- FORMAT DETAIL OUTPUT -->
            <?php
            $file_path = __DIR__ . '/details/' . strtolower($jenis ?? 'bk') . '.inc.php';
            if (file_exists($file_path)) {
                include($file_path);
            } else {
                include(__DIR__ . '/details/bk.inc.php');
            }
            ?>
            <!-- END -- FORMAT DETAIL OUTPUT -->

            <?php if (count($biblio_custom) > 0) {; ?>
                <h5 class="mt-4 mb-1"><?= __('Other Information'); ?></h5>
                <dl class="row">
                    <?php foreach ($biblio_custom as $item) { ?>
                        <dt class="col-sm-3"><?= $item['label']; ?></dt>
                        <dd class="col-sm-9">
                            <div itemprop="alternativeHeadline" property="alternativeHeadline"><?php echo ($item['value']) ? $item['value'] : '-'; ?></div>
                        </dd>
                    <?php }; ?>
                </dl>
            <?php }; ?>

            <h5 class="mt-4 mb-1"><?= __('Other version/related'); ?></h5>
            <div>
                <?php echo ($related) ? $related : '<p class="text-grey-dark">' . __('No other version available') . '</p>'; ?>
            </div>

            <h5 class="mt-4 mb-1"><?= __('File Attachment'); ?></h5>
            <div itemprop="associatedMedia">
                <?= !$file_att ? '<i>' . __('No Data') . '</i>' : $file_att; ?>
            </div>

            <h5 class="mt-4 mb-1"><?= __('Comments'); ?></h5>
            <?php echo showComment($biblio_id); ?>
            <?php if (!isset($_SESSION['mid']) && $sysconf['comment']['enable']) : ?>
                <hr>
                <a href="index.php?p=member" class="btn btn-outline-primary"><?= __('You must be logged in to post a comment'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>