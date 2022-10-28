<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-02 20:33
 * @File name           : _modal_advanced.php
 */

?>

<div class="modal fade" id="adv-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form class="modal-content" action="index.php" method="get">
            <input type="hidden" ref="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?=__('Advanced Search'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-titles"><?=__('Title'); ?></label>
                            <input type="text" name="title" class="form-control" id="adv-titles"
                                   placeholder="<?=__('Enter title'); ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-author"><?=__('Author(s)');?></label>
                            <input type="text" name="author" class="form-control" id="adv-author"
                                   placeholder="<?=__('Enter author(s) name'); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-subject"><?=__('Subject(s)');?></label>
                            <input type="text" name="subject" class="form-control" id="adv-subject"
                                   placeholder="<?=__('Enter subject'); ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-isbn"><?=__('ISBN/ISSN');?></label>
                            <input type="text" name="isbn" class="form-control" id="adv-isbn"
                                   placeholder="<?=__('Enter ISBN/ISSN'); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-coll-type"><?=__('Collection Type');?></label>
                            <select name="colltype" class="form-control"
                                    id="adv-coll-type"><?=$colltype_list; ?></select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-location"><?=__('Location');?></label>
                            <select id="adv-location" name="location"
                                    class="form-control"> <?=$location_list; ?></select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-gmd"><?=__('GMD');?></label>
                            <select id="adv-gmd" name="gmd" class="form-control"><?=$gmd_list; ?></select>
                        </div>
                    </div>
                    <div class="col"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="search" value="search" class="btn btn-primary"><?=__('Find Collection');?></button>
            </div>
        </form>
    </div>
</div>
