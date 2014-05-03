<?php
if (!isset($_SESSION['phplogin'])
        || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}

//require "../arg.php";

/* * ***************************************************************************************** */

class param extends FC_admin {

    public function __construct() {
        parent::__construct();
        $this->init_vars();

        $this->smiley_id = 0;
    }

    public function create_url($name) {
        $url = str_replace("administrator/admin.php", "", $this->url);

        return $url . 'client/themes/smileys/' . $name;
    }

    public function create_tr($id, $symbol, $image_name) {

        $image = "<img id=$image_name src=" . $this->create_url($image_name) . " />";
        $delete = "<a onmousedown=delete_tr(" . $id . ",'" . $image_name . "') class='btn btn-danger' href='#'><i class='icon-trash icon-white'></i> Delete</a>";
        //$delete = "<img height=12px width=12px  src='admin_files/theme_maker/delete.png' />";
        return "<tr id=smiley_tr_$id><td>$id</td><td id='smiley_symbol_" . $id . "'>$symbol</td><td>$image</td><td>" . $delete . "</td></tr>";
    }

    public function build_smiley_table() {

        $smileys = $this->get_smileys();
        $i = 1;
        /* <table class="table table-striped table-bordered bootstrap-datatable datatable dataTable" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
          <thead>
          <tr role="row"><th class="sorting_asc" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 190.09090912342072px;" aria-sort="ascending" aria-label="Username: activate to sort column descending">Username</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 163.09090912342072px;" aria-label="Date registered: activate to sort column ascending">Date registered</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 86.09090912342072px;" aria-label="Role: activate to sort column ascending">Role</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 89.09090912342072px;" aria-label="Status: activate to sort column ascending">Status</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 352.0909091234207px;" aria-label="Actions: activate to sort column ascending">Actions</th></tr>
          </thead>

          <tbody role="alert" aria-live="polite" aria-relevant="all"><tr class="odd">
          </tbody></table> */
        $s_wrapper = "<table id='smiley_table' class='table table-striped table-bordered bootstrap-datatable datatable dataTable'><thead><tr role='row'><th class='sorting_asc' role='columnheader' tabindex='0'>#</th><th class='sorting_asc' role='columnheader' tabindex='0'>symbol</th><th class='sorting_asc' role='columnheader' tabindex='0'>image</th><th class='sorting_asc' role='columnheader' tabindex='0'>option</th></thead><tbody>";
        $e_wrapper = "</tbody></table>";

        $content = '';
        foreach ($smileys as $smiley) {
            $content .= $this->create_tr($i, $smiley['symbol'], $smiley['image_name']);
            $i++;
        }


        $this->smiley_id = $i;

        if ($content != '') {
            return $s_wrapper . $content . $e_wrapper;
        }

        return '';
    }

//--------------------------------------------------------------------------------------------
}

$param = new param();
if (isset($_POST['draggable']) == true) {
    $config = $param->build_config_array();
    $param->update_config($config);
    $param->build_vars();
}
?>
<style>

    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }

</style>


<script type="text/javascript">
    function purge_mesg_history(){
        
        var days = $('#purge_mesg_period').val();
        $.get('admin.php?freiload=home&purge=true',{days:days},function(resp){
            alert('Messages Purged successfully.');
        });
    }
 
    function delete_tr(id,image_name){
        $('#smiley_tr_'+id).remove();
        
        $.get('admin_files/home/smiley.php?smiley=delete',{image_name:image_name});
    }
    
    
 
    function create_smiley_edit_div(id,image_name){
        
        if($('#smiley_edit_'+id).length > 0) {
            $('#smiley_edit_'+id).remove();
        }
        
        var symbol = $('#smiley_symbol_'+id).html();
        
        var div = "<div id='smiley_edit_"+id+"'>It is not necessary to upload any image for the smiley, if you only want to change the symbol of the smiley<br/><br/>symbol : <input id='smiley_symbol_input_"+id+"' type='text'/> <br/><br/> <input id='smiley_change_file_"+id+"' type='file' /> \n\
                    <br/><br/><span id='smiley_button_"+id+"'>done</span></div>";
        

        $('#dialog_box_smiley_change').html(div);
        $('#smiley_symbol_input_'+id).val(symbol);//alert($('#smiley_symbol_'+id).val());
        $('#smiley_edit_'+id).dialog({height:300,minWidth:400,title:'smiley edit'});
        $('#smiley_button_'+id).button().mousedown(function(){
            file_upload('smiley_change_file_'+id,'update',image_name);
            $('#smiley_edit_'+id).remove();
        });
    }
 
 
    
    function add_smiley_row(symbol,name) {
  
        var num=0; var max=0;
        var id = $("#smiley_table tr").last().attr('id');
 
        if(typeof id !== "undefined") {
            id = parseInt(id.replace('smiley_tr_',''));
        }else{
            id = 0;
        }
   
        id++;

        var url = '<?php echo str_replace("administrator/admin.php", "", $param->url); ?>';
        
        url =  url+'client/themes/smileys/'+name;
        var image = "<img id="+name+" src="+url+" />";
        
        var delete_btn = "<a onmousedown=delete_tr("+id+",'"+name+"') class='btn btn-danger' href='#'><i class='icon-trash icon-white'></i> Delete</a>";
        var tr = "<tr id='smiley_tr_"+id+"'><td>"+id+"</td><td id='smiley_symbol_"+id+"'>"+symbol+"</td><td>"+image+"</td><td>"+ delete_btn +"</td></tr>";
        $('.smiley_change').button();
 
        $('#smiley_table').append(tr); 
    }
    
    function file_upload(id,action,image_name){
        var smiley_val='';
        
        
        
        if(action == 'insert'){
            smiley_val = $.trim($('#smiley_symbol').val());    
        }else{
            
            var formal_id = id.replace("smiley_change_file_","");
            formal_id = parseInt(formal_id);
            smiley_val = $.trim($('#smiley_symbol_input_'+formal_id).val());
        }
        var fileInput = document.getElementById(id);
        var file = fileInput.files[0];        
    
    
        if(action == 'insert'){
            if($('#smiley').val() == '' || smiley_val == ''){
                alert('Required fields not filled');
                return;
            }
        }
    
        if(smiley_val == ''){
            alert('symbol cannot be empty');
            return;
        }
        
        var xhr = new XMLHttpRequest();
        //var data = $('#upload_div').data("data");
        xhr.open('POST', 'admin_files/home/upload.php', true);                       
        xhr.setRequestHeader("Cache-Control", "no-cache");  
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");  
        xhr.setRequestHeader("X-File-Name", file.name);  
        xhr.setRequestHeader("X-File-Size", file.size);  
        xhr.setRequestHeader("X-File-Type", file.type);  
        xhr.setRequestHeader("X-Symbol", smiley_val);
        xhr.setRequestHeader("X-Action", action);   
        xhr.setRequestHeader("X-Type", 'smileys');   
        
        if(typeof image_name !== 'undefined'){
            xhr.setRequestHeader("X-Imagename", image_name);   
        }
        xhr.setRequestHeader("Content-Type", "application/octet-stream");  
        xhr.onreadystatechange = function() {
            if (xhr.readyState != 4)  {
                return; 
            }
            if(xhr.responseText == 'exceed') {
                alert('file size has exceeded the allowed limit');
            }else if (xhr.responseText == 'type') {
                alert('invalid file type');
            }
            else if(xhr.responseText == 'exists'){
                alert(' a smiley with the specified symbol already exists!') 
            }else{
                    
                add_smiley_row(smiley_val,xhr.responseText);
            }    
        };
                
            
        xhr.send(file);
    }
        
    
    $(document).ready(function(){
     

        $('#add_smiley').click(function(){file_upload('smiley','insert');})
    
        $('.smiley_change').button();
    
    
    });

    
</script>


<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-plus"></i> Add new smiley</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">

                <form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">


                    <!-- smiley tab -->
                    <div id="smileys">

               <!-- <p>To add a new smiley , follow the three steps below : <br/>
                    1. enter a symbol for your smiley<br/> 
                    2. upload an image for your smiley <br/>
                    3. click on the button add new smiley .<p>
                        -->
                        <input placeholder="smiley symbol" type="text" id="smiley_symbol" /><br/>
                        <input type="file" id="smiley" /><br/><br/>
                        <input type="button" class="btn btn-primary" id="add_smiley" value="Add" />


                    </div>

                </form>
            </div>                   
        </div>
    </div><!--/span-->
</div>


<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-th-list"></i> Smiley list</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">



<?php
echo $param->build_smiley_table();
?>
            </div>                   
        </div>
    </div><!--/span-->
</div>
