<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 12/09/20 03.11
 * @File name           : index.php
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="description" content="Template for the new android layout">
    <meta name="author" content="Andrew Henry">
    <link rel="icon" href="#">
    <title>SLiMS Reader</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <!-- Google Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"rel="stylesheet">
    <link rel="stylesheet" href="<?= JWB ?>pdfjs/mobile/css/style.css?v=1">

    <script src="<?= JWB ?>pdfjs/mobile/js/pdf.js"></script>
    <script src="<?= JWB ?>pdfjs/build/ObjectPdf.js"></script>
    <script>
        let workerUrl = '<?php echo JWB ?>pdfjs/mobile/js/pdf.worker.js';
        let DEFAULT_URL = '<?php echo $file_loc_url; ?>';
        let LOADER_INIT = '<?php echo $loader_init; ?>';
    </script>
    <script src="<?= JWB ?>pdfjs/mobile/js/pdf_viewer.js"></script>
</head>
<body>
<div class="d-none" id="title"></div>
<!-- As a heading -->
<nav class="navbar smart-scroll navbar-light bg-light" id="top-navbar">
    <div class="d-flex justify-content-between w-100">
        <h1 class="navbar-brand mb-0 text-truncate flex-grow-1"><?= $file_d['title'] ?></h1>
        <button type="button" class="btn btn-link text-muted flex-shrink-0 py-0" id="close" onclick="window.close()">
            <div class="selector-holder">
                <i class="material-icons">close</i>
            </div>
        </button>
    </div>
</nav>
<!-- Begin page content -->
<div class="container-fluid">

    <!-- CONTENT HERE -->
    <div id="viewerContainer">
        <div id="viewer" class="pdfViewer"></div>
    </div>

    <div id="loadingBar">
        <div class="spinner-grow text-primary" role="status"></div>
        <div class="progress d-none"></div>
        <div class="glimmer d-none"></div>
    </div>

    <div id="errorWrapper" hidden="true" class="px-2">

        <div class="text-center p-4">
            <img width="180px" src="<?= JWB ?>pdfjs/mobile/images/undraw_warning_cyit.svg" alt="error">
        </div>

        <div class="text-center">
            <div id="errorMessage" class="lead"></div>
            <textarea id="errorMoreInfo" class="bg-white border-0 text-center"></textarea>
        </div>

        <div class="d-none">
            <a id="errorShowMore">More Information</a>
            <a id="errorShowLess">Less Information</a>
            <a id="errorClose">close</a>
        </div>
    </div>

</div>

<!-- Bottom Nav Bar -->
<footer class="footer">
    <div id="buttonGroup" class="btn-group selectors" role="group" aria-label="Basic example">
        <button id="previous" type="button" class="btn btn-secondary button-inactive">
            <div class="selector-holder">
                <i class="material-icons">arrow_upward</i>
            </div>
        </button>
        <button id="next" type="button" class="btn btn-secondary button-inactive">
            <div class="selector-holder">
                <i class="material-icons">arrow_downward</i>
            </div>
        </button>
        <button id="next" type="button" class="btn btn-secondary button-inactive">
            <div class="selector-holder">
                <input type="number" id="pageNumber" class="toolbarField pageNumber form-control" value="1" size="4" min="1">
            </div>
        </button>
        <button id="zoomOut" type="button" class="btn btn-secondary button-inactive">
            <div class="selector-holder">
                <i class="material-icons">remove_circle_outline</i>
            </div>
        </button>
        <button id="zoomIn" type="button" class="btn btn-secondary button-inactive">
            <div class="selector-holder">
                <i class="material-icons">add_circle_outline</i>
            </div>
        </button>
    </div>
</footer>

<script src="<?= JWB ?>pdfjs/mobile/js/viewer.js"></script>
</body>
</html>


