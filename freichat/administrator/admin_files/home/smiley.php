<?php


require_once '../../../arg.php';

class smiley extends FreiChat{
    
    
    public function sanitize($input) {
        $output = htmlspecialchars($input);
        return $output;
    }
    
    public function delete_smiley($image_name){
        $image_name = $this->sanitize($image_name);
        $query = "DELETE FROM frei_smileys WHERE image_name='$image_name'";        
        $this->db->query($query);echo $query;
    }
    
}

$sm = new smiley();

if(isset($_REQUEST['smiley'])){
    if($_REQUEST['smiley'] == 'delete'){
        $sm->delete_smiley($_GET['image_name']);
    }
}
