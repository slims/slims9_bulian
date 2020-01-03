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
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Advanced Search</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-titles">Title</label>
                            <input type="text" name="title" class="form-control" id="adv-titles"
                                   placeholder="Enter title">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-author">Author(s)</label>
                            <input type="text" name="author" class="form-control" id="adv-author"
                                   placeholder="Enter author(s) name">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-subject">Subject(s)</label>
                            <input type="text" name="subject" class="form-control" id="adv-subject"
                                   placeholder="Enter subject">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-isbn">ISBN/ISSN</label>
                            <input type="text" name="isbn" class="form-control" id="adv-isbn"
                                   placeholder="Enter ISBN/ISSN">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-coll-type">Collection Type</label>
                            <select name="colltype" class="form-control"
                                    id="adv-coll-type"><?php echo $colltype_list; ?></select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-location">Location</label>
                            <select id="adv-location" name="location"
                                    class="form-control"> <?php echo $location_list; ?></select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="adv-gmd">GMD</label>
                            <select id="adv-gmd" name="gmd" class="form-control"><?php echo $gmd_list; ?></select>
                        </div>
                    </div>
                    <div class="col"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="search" value="search" class="btn btn-primary">Find Collection</button>
            </div>
        </form>
    </div>
</div>
