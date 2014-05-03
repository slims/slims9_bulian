<?php
require 'header.php';

if(!isset($_GET['skip'])) {
    $skip = '';
}else{
    $skip = 'yes';
}

?>

<div> 

    <div class="box span10 centerme" id="content">



        <div class="box-header well">
            <h2>freichat smart custom installation - step 4</h2>
        </div>

        <div class="box-content" >
            <div class="page-header">
                <h1 id="basic_complete_header">Database table linking</h1>
            </div>

            <div id="skip1" class="well">
                <div class="spaceout">
                    Here, you need to tell freichat the table information for users so that freichat is able to fetch your users .
                </div>


                <div style="margin-top:10px" class="example_img">
                    <div>Following is a table structure of a table named <b><em>users</em></b></div> <br/>
                    <img style="border:1px solid rgba(0, 0, 0, 0.15)" src="images/user_db.png" />
                </div>


            </div>
            <div id="skip2" class="well">
                <div class="spaceout hideme" id="is_it_correct"><h4>freichat has guessed and auto-filled the below details please verify</h4></div>
                <table class="spaceout table table-striped table-bordered" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">

                    <thead>
                    <th>User table info</th>
                    </thead>
                    <tbody role="alert" aria-live="polite" aria-relevant="all">

                        <tr>
                            <td >table name that stores information of all users </td>
                            <td><input class="span4" id="user_table" type="text" placeholder="for this eg. users" data-rel="tooltip"/>
                        </tr>

                        <tr>
                            <td >column name in the <span class="user_table_update">user</span> table that stores the name of the user </td>
                            <td><input class="span4" id="username_table" type="text" placeholder="for this eg. username or name" data-rel="tooltip"/>
                        </tr>

                        <tr>
                            <td >column name in the <span class="user_table_update">user</span> table that stores the id of the user </td>
                            <td><input class="span4" id="userid_table" type="text" placeholder="for this eg. id" data-rel="tooltip"/>
                        </tr>

                    </tbody>
                </table>

                <div class="spacout">
                    <div class="alert alert-info" id="test_table_info">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <div>After filling the above details hit done</div> <!-- why waste a name on id -->
                    </div>

                    <button id="done_table_info" class="btn"> Done</button>
                </div>
            </div>
                
                <div class="alert alert-info hideme" id="basic_complete">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div>Basic installation is now completed. <b>Please rename or remove the installation folder from freichat for security reasons</b> </div> <!-- why waste a name on id -->
                </div>

            <div class="well hideme" id="fired_up">
                If you want to continue with the advanced part of this installation , click continue with advanced setup else close this tab. 
                <button style="display: block" id="fired_up_continue" class="btn btn-primary"><i class="icon-arrow-right icon-white"></i> continue with advanced setup</button>
                <div style="margin-top: 5px" class="muted">Above part is completely optional , proceed only if you want to integrate avatars with freichat</div>
            </div>
                
            </div>
            
        </div> <!-- content ended -->

    </div>





    <div class="hideme" id="function_calls">smart_tables('<?php echo $skip; ?>')</div>
</div>


<script>
    
    
    //i wont be executed on ajax call  ;)
    document.location = 'smart.php';    
</script>
