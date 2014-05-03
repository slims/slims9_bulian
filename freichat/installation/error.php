<?php
require 'header.php';
?>

<div style="text-align: center;color: red;font-family: 'Changa One', cursive;font-size: 16px;padding-top: 100px">

<?php
if (isset($_SESSION['error'])) {
    echo $_SESSION['error'];
}
?>

</div>
<br/>
<br/>
<br/>
<div style="text-align: center;">
        <a href="index.php" class="nextbutton" >Retry</a>
    
</div>
<?php
    require 'footer.php';
?>