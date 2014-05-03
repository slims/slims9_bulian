<?php

if(isset($_GET['load'])){
    
    if($_GET['load']=='theme_editor'){
     
        require 'theme_view.php';
        
    }

    
}
    else{
        require 'theme_lister.php';
    }