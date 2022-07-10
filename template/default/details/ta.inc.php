<dl class="row">
    <dt class="col-sm-3"><?= __('Pembimbing I'); ?></dt>
    <dd class="col-sm-9"><?= $pembimbing1 ?? '-' ?></dd>

    <dt class="col-sm-3"><?= __('Pembimbing II'); ?></dt>
    <dd class="col-sm-9"><?= $pembimbing2 ?? '-' ?></dd>

    <dt class="col-sm-3"><?= __('Penguji I'); ?></dt>
    <dd class="col-sm-9"><?= $penguji1 ?? '-' ?></dd>

    <dt class="col-sm-3"><?= __('Penguji II'); ?></dt>
    <dd class="col-sm-9"><?= $penguji2 ?? '-' ?></dd>

    <dt class="col-sm-3"><?= __('Jurusan'); ?></dt>
    <dd class="col-sm-9">
        <?php
        $jurusan_q = $dbs->query("select jurusan from mst_jurusan where jurusan_id = '" . $jurusan_id . "'");
        if ($jurusan_q->num_rows > 0) {
            $jurusan_d = $jurusan_q->fetch_row();
            echo $jurusan_d[0];
        }
        ?>
    </dd>

    <dt class="col-sm-3"><?= __('Call Number'); ?></dt>
    <dd class="col-sm-9">
        <div><?php echo ($call_number) ? $call_number : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Collation'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="numberOfPages" property="numberOfPages"><?php echo ($collation) ? $collation : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Language'); ?></dt>
    <dd class="col-sm-9">
        <div>
            <meta itemprop="inLanguage" property="inLanguage" content="<?php echo $language_name ?>" /><?php echo $language_name ?>
        </div>
    </dd>

    <dt class="col-sm-3"><?= __('Classification'); ?></dt>
    <dd class="col-sm-9">
        <div><?php echo ($classification) ? $classification : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Content Type'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookFormat" property="bookFormat"><?php echo ($content_type) ? $content_type : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Media Type'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookFormat" property="bookFormat"><?php echo ($media_type) ? $media_type : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Carrier Type'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookFormat" property="bookFormat"><?php echo ($carrier_type) ? $carrier_type : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Edition'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookEdition" property="bookEdition"><?php echo ($edition) ? $edition : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Subject(s)'); ?></dt>
    <dd class="col-sm-9">
        <div class="s-subject" itemprop="keywords" property="keywords"><?php echo ($subjects) ? $subjects : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Specific Detail Info'); ?></dt>
    <dd class="col-sm-9">
        <div><?php echo ($spec_detail_info) ? $spec_detail_info : '-'; ?></div>
    </dd>
</dl>