<script src="<?php echo JWB ?>quaggaJS/adapter-latest.js" type="text/javascript"></script>
<script src="<?php echo JWB ?>quaggaJS/quagga.js" type="text/javascript"></script>
<div class="modal" id="barcodeModal" tabindex="-1" role="dialog" aria-labelledby="barcodeModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="barcodeModalLabel"><?php echo __('Barcode Reader') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body m-0">
        <iframe frameborder="0" height="320" width="460" id="iframeBarcodeReader"></iframe>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo __('Close') ?></button>
    </div>
    </div>
</div>
</div>

<script type="text/javascript">
$('#barcodeModal').on('hidden.bs.modal', function(e){
    $('#iframeBarcodeReader').removeAttr('src');            
});
</script>
