<?php
require 'header.php';
?>

<style>
    .avatar_table_update {
        font-weight: bold;
    }
    
    #data_table_avatar td:nth-child(2) {
        width: 30%;
    }
    #data_table_avatar td:nth-child(2) input {
        width: 100%;
    }

</style>

<div> 

    <div class="box span10 centerme" id="content">


        <div class="modal hide fade in" id="whats_modal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3>Double linked table</h3>
			</div>
			<div class="modal-body">
				<p>Let take an example of two tables </p>
                                <div class="spaceout well">
                                    <b> users </b> table
                                    <img src="images/double_link1.png" />
                                </div>
                        
                                <div class="spaceout well">
                                    <b> files </b> table
                                    <img src="images/double_link2.png" />
                                </div>

                        
                                <div class="spaceout well">
                                <p> In above case , the <em><b>users</b></em> table stores the <b>uid</b>(which is actually the userid) and <b>picture</b> 
                                    which is used to link with the <em><b>files</b></em> table
                                </p>
                                <p>
                                    In the <em><b>files</b></em> table the <b>uid</b> (userid of the user) which is same as <em><b>users</b></em> table and <b>fid</b> 
                                    is the reference used to link to the column <b>picture</b> in the <em><b>users</b></em> table
                                </p>
                                </div>
                                
                                <p>
                                    
                                    
                                    
                <table class="spaceout table table-striped table-bordered" id="data_table_avatar" aria-describedby="DataTables_Table_0_info">

                    <thead>
                    <th>Values according to above example :</th>
                    </thead>
                    <tbody role="alert" aria-live="polite" aria-relevant="all">

                        <tr>
                            <td >table name that contains avatar url of users </td>
                            <td><span style="width:85%;text-align: center;" class="uneditable-input">files</span>
                        </tr>

                        <tr>
                            <td >column name in the <span class="">files</span> table that stores the url of the avatar </td>
                            <td><span style="width:85%;text-align: center;" class="uneditable-input">filename</span>
                        </tr>
                        
                        <tr>
                            <td >column name in the <span class="">files</span> table that stores the userid of the user </td>
                            <td><span style="width:85%;text-align: center;" class="uneditable-input">uid</span>
                        </tr>

                        
                        <tr>
                            <td >column name in the <span class="">files</span> table that stores the reference of the user's avatar</td>
                            <td><span style="width:85%;text-align: center;" class="uneditable-input">fid</span>
                        </tr>
                        <tr>
                            <td >column name in the <span class="">users</span> table that stores the reference of the user's avatar</td>
                            <td><span style="width:85%;text-align: center;" class="uneditable-input">picture</span>
                        </tr>

                        
                    </tbody>
                </table>
                                    
                                </p>
                                
                                <div class="spaceout well">
                                    Sometimes the two references (i.e fid & picture in this case) can be same . If that is your case 
                                    enter the same reference for both the inputs
                                    
                                </div>
                                
                        </div>
			<div class="modal-footer">
				<a href="#" class="btn btn-primary" data-dismiss="modal">Understood !</a>
			</div>
	</div>        

        <div class="box-header well">
            <h2>freichat smart advanced custom installation</h2>
        </div>

        <div class="box-content" >
            <div class="page-header">
                <h1>Avatar integration</h1>
            </div>



            <div class="well">
                <div class="spaceout">
                    Here, you need to tell freichat the table information for avatar so that freichat is able to fetch your user's avatar.
                </div>


                <div style="margin-top:10px" class="example_img">
                    <div>Lets take the example of the following table structure of a table named <b><em>members</em></b></div> <br/>
                    <img style="border:1px solid rgba(0, 0, 0, 0.15)" src="images/avatar.png" />
                </div>


            </div>
            <div class="well">
                <table class="spaceout table table-striped table-bordered" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">

                    <thead>
                    <th>User table info</th>
                    </thead>
                    <tbody role="alert" aria-live="polite" aria-relevant="all">

                        <tr>
                            <td >table name that contains avatar url of users </td>
                            <td><input class="span4" id="avatar_table" type="text" placeholder="for this eg. members" data-rel="tooltip"/>
                        </tr>

                        <tr>
                            <td >column name in the <span class="avatar_table_update">avatar</span> table that stores the url of the avatar </td>
                            <td><input class="span4" id="avatar_column" type="text"  placeholder="for this eg. avatar" data-rel="tooltip"/>
                        </tr>
                        
                        <tr>
                            <td >column name in the <span class="avatar_table_update">avatar</span> table that stores the userid of the user </td>
                            <td><input class="span4" id="userid_column" type="text" placeholder="for this eg. user_id" data-rel="tooltip"/>
                        </tr>

                        
                        <tr>
                            <td >column name in the <span class="avatar_table_update">avatar</span> table that stores the reference of the user's avatar <a data-toggle="modal" data-target="#whats_modal" class="whats_ref" href="#"> what is this?</a></td>
                            <td><input class="span4" id="reference_column_avatar" type="text"  placeholder="leave empty if you do not understand" data-rel="tooltip"/>
                        </tr>
                        <tr>
                            <td >column name in the <b>user</b> table that stores the reference of the user's avatar <a data-toggle="modal" data-target="#whats_modal" class="whats_ref" href="#"> what is this?</a></td>
                            <td><input class="span4" id="reference_column_user" type="text"  placeholder="leave empty if you do not understand" data-rel="tooltip"/>
                        </tr>

                        
                    </tbody>
                </table>

                <div class="spacout">
                    <div class="alert alert-info" id="test_table_avatar_info">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <div>After filling the above details hit done</div> <!-- why waste a name on id -->
                    </div>

                    <button id="done_table_avatar_info" class="btn"> Done</button>

                </div>
            </div>


            <div class="frame_load hideme well" id="myframe3_div">
                <iframe  name="myframe3_name" id="myframe3" style="width:100%" height="400px" src="" seamless></iframe>
            </div>

            <div class="well hideme" id="avatar_login">
                <div class="spaceout">Now, please login to your website with an user account that <b>has</b> an avatar </div>
                <div class="spaceout">
                    <button id="done_avatar_login" class="btn btn-primary"><i class="icon-ok icon-white"></i> Done</button>
                    <span class="muted">click me once you have logged in your website </span>
                    <span id="avatar_verify_img" class="hideme">
                        <img src="../administrator/img/ajax-loaders/ajax-loader-1.gif" title="creating tables....">
                    </span>

                </div> 

            </div>

            <div id="check_user_avatar" class="well hideme" >

                <div class="alert alert-info" id="avatar_login_notice">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div>verifying your user avatar... please wait</div> <!-- why waste a name on id -->
                </div>

                <div class="hideme" id="avatar_correction">

                    <div class="well hideme">
                        You can also use any variables(value of a column name) for a particular user by enclosing it in curly braces {}
                        for eg. {<span id="variable_eg_1"></span>} , {<span id="variable_eg_1"></span>}
                    </div>
                    
                    <!--<label for="url">Please correct the following url for the avatar image: </label>-->
                    <input id="avatar_url_correction" type="text" value=""/>

                    <div id="correct_form" class="spaceout">
                    <button id="correction_submit" class="btn btn-primary" ><i class="icon-pencil icon-white"></i> correct</button>
                    <span class="muted">By correcting the above url freichat will be able to fetch the correct avatar images for all other users</span>
                    </div>
                </div>


                <div class="hideme" id="retry">
                    <button style="margin-top:10px" id="try_again" class="btn">try again</button>                
                </div>

            </div>

                <div id="reload_frame_option2" class="spaceout well hideme">
                    <div class="muted"> If you have made any changes , you can reload the frame at anytime by clicking reload button below </div>
                    <button style="margin-top:10px" id="frame_close3" class="btn">reload</button>
                </div>

            <div class="well hideme" id="end_of_it">
                <h5> freichat custom installation ends here. </h5>

                <div class="spaceout">
                    <button style="display:inline-block" id="admin_visit" class="btn btn-primary"><i class="icon-wrench icon-white"></i> administer</button>
                    <button style="display:inline-block" id="page_visit" class="btn btn-primary"><i class="icon-globe icon-white"></i> visit site</button>

                </div>

            </div>




        </div> <!-- content ended -->

    </div>





    <div class="hideme" id="function_calls">smart_avatar()</div>
</div>


<script>
    
    
    //i wont be executed on ajax call  ;)
    document.location = 'smart.php';    
</script>

<!--

Make sure your directory freichat/administrator has atleast the read permissions . 

It is possible that there was some problem during the file upload / extract process. Try uploading the freichat file again.

-->
