<?php
require 'header.php';
?>



<style>


    input[type="text"] {
        width: 84%;
        padding-left: 4px;
    }

    td {
        vertical-align: middle !important;
    }

    .site_lang_radio{
        background: #eee;
        padding: 5px;
        cursor: pointer;
        border: 1px solid #ccc;
        margin-left: 10px;
        border-radius: 3px;    
    }

    .site_lang_radio:hover {
        background: white;
    }
</style>

<script>

    if(typeof console.log === "undefined") {
        console = {
            log: function(mesg) {
                //dont do anything
            }
        }
    }

    function ajax_req(url, success, data, type) {
        
        if(typeof done === "undefined") {
            done = function() {
                console.log("success ajax request to "+url);
            };
        }
        
        if(typeof data === "undefined") {
            data = "{}";
        }
        
        if(typeof type === "undefined") {
            type = "POST";
        }
        
        //TODO: checnge success and error to done and fail
        
        $.ajax({
            type: type,
            url: url,
            data: data,
//            contentType: "application/json; charset=utf-8",
            success: success,
            error: function() {
                
                FCS.try_count++;
                if (FCS.try_count <= FCS.retry_limit) {
                    //try again
                    $.ajax(this);
                }else{
                    FCS.try_count = 0;
                }
            }
        });
    }

    function ajax_next(url) {
        $('#content').fadeOut().parent().append('<div id="loading" class="center">Loading...<div class="center"></div></div>');
        History.pushState(null, null, url);
    }

    function boot_alert(id,mesg,status) {
        
        
        var el = $('#'+id);
        var div = $('#'+id+" div");
        
        if(status == "alert-success") {
            if(el.hasClass("alert-error")){
                el.removeClass("alert-error");
            }
            
        }else{
            
            if(el.hasClass("alert-success")){
                el.removeClass("alert-success");
            }
            
        }
        
        el.removeClass("alert-info")
        el.addClass(status);
        div.html(mesg);
        
        //el.removeClass("hideme"); //eq. to .show()
    }
    
    
    function smart_avatar() {
        
        /*$('.whats_ref').click(function(){
            $('#whats_modal').show();
        });*/
        
        $('#avatar_table').change(function() {
            $('.avatar_table_update').html("<b>"+$(this).val()+"</b>");
        });
        
        $('#done_table_avatar_info').click(function() {
           
            var url = 'smart_ajax.php?action=test_avatar_details';
            var data = {
                avatar_table: $('#avatar_table').val(),
                avatar_column: $('#avatar_column').val(),
                userid_column: $('#userid_column').val(),
                reference_column_user: $('#reference_column_user').val(),
                reference_column_avatar: $('#reference_column_avatar').val()                
            };
            
            var type = 'GET';
            
            var done = function(result) {
                if(result == 'correct') {
                    boot_alert('test_table_avatar_info','Success , freichat successfully connected to '+$('#avatar_table').val()+" table",'alert-success');
                    $('#done_table_avatar_info').addClass("disabled");
                    $('#myframe3').attr("src",FCS.site_url);
                    $('#myframe3_div').show();
                    $('#avatar_login').show();
                    scroll_down();
                }else{
                    boot_alert('test_table_avatar_info','freichat failed to connect to your '+$('#avatar_table').val()+" table. Please check the above details",'alert-error');                    
                    
                }
                
            }

            ajax_req(url, done, data, type);
        });

        $('#done_avatar_login').click(function() {
            /*if($(this).hasClass('disabled')) {
                return false;
            }
            $(this).addClass('disabled');*/
            $('#check_user_avatar').show();
            
            //@myframe3_name is the name of the iframe
            $('#avatar_verify_img').show();
            //$('#reload_frame_option2').show()
            test_avatar();        
            
        });
        
        $('#try_again').click(function() {
            test_avatar();
        });
        
        $('#admin_visit').click(function() {
            document.location = FCS.site_url+'<?php echo $_SESSION["freichat_renamed"]; ?>'+'/administrator';
        });
        
        $('#page_visit').click(function() {
            document.location = FCS.site_url;
        });

        $('#correction_submit').click(function() {
            if(typeof FCS.res2 != "undefined"){
                var new_rule,
                val = $('#avatar_url_correction').val();
                if(val.replace(FCS.res2,"") == val) {
                    new_rule = 'true';
                }else{
                    new_rule = 'false';
                }
                
                var last = val.split("/");
                last = last[(last.length)-1];
                
                if(last != FCS.res2) {
                    new_rule = 'name_changed';
                }
                
                
                test_avatar($('#avatar_url_correction').val().replace(FCS.res2,""),new_rule); //send the corrected url
            }
        });

    }
    
    function test_avatar(correction,new_rule) {
        
        $('#retry').hide();
        var first_time = false;
    
        if(typeof correction == "undefined") {
            correction = '';
            first_time = true;
        }
        if(typeof new_rule == "undefined")
            new_rule = 'false';

        var url,str = '';
        
        var original = '';
        
        if(new_rule == "name_changed") {
            original = FCS.res2;
        }
        
        var url = 'smart_ajax.php?action=test_avatar';
        
        var data = {
            site_url : FCS.site_url,
            id : myframe3_name.freidefines.GEN.getid,
            correction: correction,
            new_rule:new_rule,
            original:original            
        };
        
        var type = 'GET';
        
        var done = function(res) {
                
            $('#avatar_verify_img').hide();
                
            res = JSON.parse(res);

            FCS.res2 = res[2];    


            if(res[0] == 'exit') {
                boot_alert('avatar_login_notice',res[1]);
   
            }
            if(res[0] == 'correct') {
                //completed
                boot_alert('avatar_login_notice','freichat has been successfully integrated with your avatar system','alert-success');
                $('#correct_form').fadeOut();
                $('#end_of_it').show();
                $('#avatar_correction').hide();
                
            }else if(res[1] == ""){
                //url is wrong + it is empty :(
                boot_alert('avatar_login_notice','freichat got an empty avatar url for this user, please login with an user that <b>has</b> uploaded an avatar in his profile , then click retry button','alert-error');
                $('#retry').show();
            }else{
                
                url = make_absolute(res[2]);
                 
                if(res[3] && url_exists(url)) {
                    test_avatar(url.replace(res[2],"")); //send the correct url
                }else{
                
                    if(!first_time) {
                        str = "Not yet, ";
                    }
                
                    boot_alert('avatar_login_notice',str+'please correct the following url','alert-error');
                
                
                    if(!first_time) {
                        //anything other than 0 is true :)
                    
                        $('#avatar_url_correction').css('border-color','red');

                        setTimeout(function() {
                            $('#avatar_url_correction').css('border-color',"rgb(204, 204, 204)");

                        },2000)
                    }
                
                    $('#avatar_correction').show();
                    $('#variable_eg_1').html($('#avatar_column').val());
                    $('#variable_eg_2').html($('#userid_column').val());

                    $('#avatar_url_correction').val(res[1]);

                    
                } 
            }
                
            //$('#reverify').show();
            scroll_down();            
        }      
        
        ajax_req(url, done, data, type);
    }
    
    
    function make_absolute(url) {
        url = url.replace("/\\/g","/");
    
        var slash = "/";
    
        if(url[0] == "/") {
            slash = "";
        }
        
        var site_url = FCS.site_url;
        if(FCS.site_url[FCS.site_url.length-1] == "/") {
            site_url = site_url.replace(/\/+$/,'');
        }
        
        return site_url+slash+url;
        
    }
    
    function url_exists(url) {
        
        var http = new XMLHttpRequest();
        http.open('HEAD', url, false);
        http.send();
        return http.status!=404;
    }
    
    function modify_display() {
        
        var lang = FCS.site_lang,cls;
        
        if(lang == "asp") {
            cls = "frei_ident_csharp_cookie";
        }else{
            
            //TODO: check if user wants cookie method or not
            cls = "frei_ident_cookieless";
        }
        
        //show all divs relevant to current language and method
        $("."+cls).show();
    }

    
    function smart_tables(skip) {
        
        if(skip == 'yes') {
            $('#basic_complete_header').html('Installation complete')
            $('#tbl_gen').show();
            $('#skip1').hide();
            $('#skip2').hide();
            $('#basic_complete').show();            
            ajax_req('smart_ajax.php?action=complete_install',function(){},{},'GET');
            return;
        }
        
        
        
        var url = 'smart_ajax.php?action=get_tables_smartly';
        var done = function(data) {

            if(data) {
                var tables = JSON.parse(data);
               
                if(tables) {
                   
                    var usertable = tables['usertable'];
                    $('#user_table').val(usertable);
                   
                    if(typeof tables['usercolumn'] != "undefined") {
                        $('#username_table').val(tables['usercolumn']);
                        $('#userid_table').val(tables['idcolumn'])                       
                    }
                    
                    $('#is_it_correct').show();
                }
            }            
        }
        
        ajax_req(url,done);
        
        
        $('#user_table').change(function() {
            $('.user_table_update').html("<b>"+$(this).val()+"</b>");
        })
        
        $('#done_table_info').click(function() {

            var url = 'smart_ajax.php?action=test_table_info';
            var data = {
                table: $('#user_table').val(),
                name: $('#username_table').val(),
                id: $('#userid_table').val()                
            };

            var done = function(result) {
                if(result == 'correct') {
                    boot_alert('test_table_info','Great ! freichat is now successfully linked to your '+$('#user_table').val()+" table",'alert-success');
                    //update hardcode.php 
                    $.get('smart_ajax.php?action=complete_install');
                    $('#basic_complete').show();

                    $('#fired_up').show();                    

                    scroll_down();
                }else{
                    boot_alert('test_table_info','freichat failed to link to your '+$('#user_table').val()+" table. Please check the above details",'alert-error');                    
                    
                }
            };
            
            ajax_req(url, done, data);
        });
        
        
        $('#fired_up_continue').click(function() {
            var url = "smart_avatar.php";
            ajax_next(url);            
        })
    }
    
    function smart_integration() {
        
        modify_display();
        //call the syntax highlighter 
        Rainbow.color();
        
        $('#done_secondary_paste').click(function() {
            $(this).addClass("disabled");
            $('#myframe2').attr("src",FCS.site_url);
            $('#myframe2_div').show();
            $('#steps').show();
            scroll_down();
            //$.cookie("freichat_user","" , { path: "/" }); //Reset cookie to get the fresh cookie

        });
        
        $('#frame_close').click(function() {
            reload_frame('myframe2');
            chk_user_authentication();

        });
        
        $('#done_login').click(function() {
            $('#check_user').show();
            scroll_down();
            chk_user_authentication();
        });
        
        $('#integration_success_btn').click(function() {
            var url = "smart_tables.php";
            ajax_next(url);

        });
        
        $('#skip_integration').click(function() {
            var url = "smart_tables.php?skip='yes'";
            ajax_next(url);
            
        });
    }
    
    function chk_user_authentication() {

        if(FCS.user_authentication_time > 6) {
            clearTimeout(FCS.user_authentication_timer);
            FCS.user_authentication_time = 0;
            
            if(!user_authenticated())
                boot_alert("login_notice",":( Could not authenticate user . Please verify your edited copy-paste code and make any required changes ","alert-error");
            return; //no more...
        }

        
        if(user_authenticated()) {
            boot_alert("login_notice","Wonderful! User has been successfully authenticated and freichat is now integrated with your user system","alert-success");
            $('#success_integration').show();
            $('#reload_frame_option2').hide();
            scroll_down();
        }else{
            boot_alert("login_notice","Trying to verify user . please wait for "+(4-parseInt(FCS.user_authentication_time/2))+" seconds","alert-info");
            FCS.user_authentication_time++;
            FCS.user_authetication_timer = setTimeout(chk_user_authentication,500);
        }
        
    }
    
    function user_authenticated() {        
        return !!$.cookie("freichat_user");
    }

    function smart_session() {
        
        modify_display();
        
        $('#done_primary_paste').click(function(){
            $(this).addClass('disabled');
            $('#website_url').show();
            scroll_down();
        });
        
        $('#url_submit').click(function() {
            var src = $('#website_url_text').val();
            $('#myframe').attr("src",src);
            $('#frame_load').show();
            $('#canyousee').fadeIn();
            scroll_down();
        });
        
        $('#freichat_invisible').click(function() {
           
            $('#no_visible_notice').show();
            $('#js_solve_div').show();

            scroll_down();
           
            if(!freichat_present()) {
                //wrong copy pasted
                boot_alert('freichat_visibility','freichat code is not found .... <br/>looks like you have copy pasted the code in the wrong location/file . please correct it','alert-error');
            }else {
                //some js error
                scroll_down();
                boot_alert('freichat_visibility','freichat code has been found .... <br/> that means freichat has been loaded but is not visible due to some js errors. Please have a look at the file freichat/SOLUTIONS.txt for a solution to your problem','alert-error');
     
            }
           
        });
        
        $('#frame_reload').click(function() {
            reload_frame('myframe');
        });
        
        
        $('#autofix').click(function() {
            $('#js_solve').show();
            
        });

        $('#generate_report').click(function(){

            if(typeof session_frame.freidefines == "undefined") {
                $('#report_progress').html("<div class='spaceout'>Could not find freichat . Report not generated !</div>");
                return;
            }

            $('#report_progress').show();
           
            var error_report = session_frame.freidefines.freichat_error_report;
           
            if(typeof error_report == "undefined") {
                $('#report_progress').html("\n\
<div class='spaceout'>No errors found . Report not generated !</div>\n\
");
                scroll_down();
                return;
            }
            $.get('smart_ajax.php?action=generate_report',{
                error_report: error_report
            },function(url) {
               
                $('#report_progress').html("\n\
<div class='spaceout'>Report successively generated</div>\n\
<button style='display:block' class='btn' id='download_report'>Download report</button>");
    
                $('#download_report').click(function(){
                    window.open(url);

                })
                
                scroll_down();
            });
           
        });
        
        
        
        $('#freichat_visible').click(function() {
            FCS.site_url = $('#website_url_text').val();

            var url = "smart_integration.php";
            ajax_next(url);
            //next page
        });
        
        
    }
    
    function scroll_down() {console.log("called");
        $("html, body").animate({ scrollTop: $('#content').height() });
    }
    
    function reload_frame(id) {
        $( '#'+id ).attr( 'src', function ( i, val ) { return val; });
    }
    
    function freichat_present() {
        return $('#myframe').contents().find("#freichat").length;
    }

    var FCS = {
        db_connected: false,
        user_authentication_time:0,
        try_count: 0,
        retry_limit: 3
    };

    (function($){

        
        $(document).ready(function() {
            
            FCS.smart = function() {
                

                $('#driver').change(function() {
                
                    if($(this).val() == "sqlsrv") {
                        $('#sqlsrv_auth_type').show();   
                        $('.sqlsrv_auth_sql').hide();
                    }else{
                        $('#sqlsrv_auth_type').hide();   
                        $('.sqlsrv_auth_sql').show();                    
                    }
                });
            
                $('#sqlsrv_auth_type').change(function() {
               
                    if($(this).val() == "windows") {
                        $('.sqlsrv_auth_sql').hide();
                    }else{
                        $('.sqlsrv_auth_sql').show();
                    }
                });
            

                $('#create_tables').click(function() {
                    $('#create_tables_img').show();
                    $('#tbl_gen_s').show();
                    scroll_down();
                    
                    var url = 'smart_ajax.php?action=create_tables';
                    
                    var data = {
                        driver: FCS.db_driver
                    }
                    var done = function(res){
               
                        if(res  == 'created') {
                            boot_alert('tbl_gen_info','All tables have been successfully created','alert-success');
                        }else{
                            boot_alert('tbl_gen_info','There was some problem while creating the tables. You can do this step manually by importing the file freichat/installation/install.sql to your database using phpmyadmin(or any of its equivalent)','alert-error');                   
                        }
                        $('#create_tables_img').hide();
                
                        $('#db_next').show();
                        scroll_down();
               
                    };
                    ajax_req(url,done,data);

                });

                
                $('#db_next').click(function() {
                    var url = "smart_session.php";
                    ajax_next(url);
                   
                });

                $('#db_submit').click(function() {
                    
                    var pass = $('#am1').val();
                    var f_pass = false;
                    
                    if($.trim(pass) == "") {
                        $.noty({text:"administrator password cannot be empty"});
                    }else if(pass == "adminpass") {
                        $.noty({text:"administrator password cannot be <b>'adminpass'</b> for security reasons"});                        
                    }
                    else if(pass.length <= 4) {
                        $.noty({text:"length of administrator password must be greater than 4"});                        
                    }else{
                        f_pass = true;
                    }
                
                    if(!f_pass) return false;
                
                    $('#test_conn_img').show();
                      
                            
                    //$('#paramform').submit();
                    FCS.db_connected = true;
                    FCS.db_driver = $('#driver').val();
                    FCS.db = {
                        driver: FCS.db_driver,
                        host:   $('#host').val(),
                        name:   $('#dbname').val(),
                        user:   $('#muser').val(),
                        pass:   $('#mpass').val(),
                        port:   $('#port').val(),
                        prefix: $('#dbprefix').val()
                    };
                    
                    if($('#sqlsrv_auth_type').val() == "windows" &&  FCS.db.driver == "sqlsrv") {
                        FCS.db.user = "";
                        FCS.db.pass = "";
                    }
                        
                        
                    var url = 'smart_ajax.php?action=update_db';
                    FCS.site_lang = $('#site_lang_used input[type=radio]:checked').val();
                    
                    var data = {
                        db: FCS.db,
                        PATH:   '<?php echo $_SESSION["freichat_renamed"]; ?>',
                        admin_pass: $('#am1').val(),
                        lang: FCS.site_lang                        
                    };
                    
                    var done = function(data) {
                        if(data == 'written') {
                            boot_alert("test_conn_status","<strong>Well done!</strong>  Successfully connected to your database ","alert-success");
                            $("input").attr("disabled","disabled");
                            $("select").attr("disabled","disabled");
                            $('#tbl_gen').show();
                                
                            //$('#db_next').show();
                            FCS.db = {}; //do immediate destroy for security reasons . 
                            //$('#db_submit').hide();
                                
                        }else{

                            boot_alert("test_conn_status","<strong>Hmm :(</strong>  Failed to connect to your database ERROR: "+data,"alert-error");
                            
            
                            //$.noty({text:"ERROR: "+data});
                        }
                        $('#test_conn_img').hide();    
                        
                    }
                        
                    ajax_req(url, done, data);        
                    scroll_down();
                
                });
            }
            FCS.smart();
        })
        
        
    })(jQuery);
    
    
    
    
    
    


</script>

<div class="frei_content">
    <div class="box span10 centerme" id="content">
        <div class="box-header well">
            <h2>freichat smart custom installation - step 1</h2>
        </div>

        <div id="modal_db"></div>
        <div class="box-content" >

            <div class="spaceout">
                <b>Please do not create a new database for freichat</b> Enter the database name that is currently used by 
                your website to store all the details of your users

            </div>

            <table id="tble" class="table table-striped table-bordered">	 


                <tbody>    

                    <tr>
                        <td><div>Database driver</div></td>
                        <td ><div><select id="driver">
                                    <option value="mysql">MySQL</option>
                                    <option value="sqlsrv">MS SQL</option>

                                </select></div></td>
                    </tr>

                    <tr>
                        <td><div>Database Host</div></td>
                        <td ><div><input name="host" id="host" size="30px" type="text"  /></div></td>
                    </tr>
                    <tr>
                        <td><div>Database Port [only if required]</div></td>
                        <td ><div><input name="port" id="port" size="30px" type="text" placeholder="usually not required" /></div></td>
                    </tr>

                    <tr id="sqlsrv_auth_type" class="hideme">
                        <td>Authentication</td>
                        <td>
                            <select>
                                <option value="windows">Windows Authentication</option>
                                <option value="sql">SQL Server Authentication</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="sqlsrv_auth_sql">
                        <td><div>Database Username</div></td>
                        <td ><div><input name="muser" id="muser" size="30px" type="text"/></div></td>
                    </tr>

                    <tr class="sqlsrv_auth_sql">
                        <td><div>Database Password</div></td>
                        <td ><div><input name="mpass" id="mpass" size="30px" type="text" /></div></td>
                    </tr>

                    <tr>
                        <td><div>Database Name</div></td>
                        <td ><div><input name="dbname" id="dbname" size="30px" type="text" /></div></td>
                    </tr>

                    <tr>
                        <td><div>Table Prefix [only if required]</div></td>
                        <td ><div><input name="dbprefix" id="dbprefix" size="30px" type="text"  /></div></td>
                    </tr>

                    <tr>
                        <td><div>Integrates With</div></td>
                        <td ><div><div class="uneditable-input" style="width:82%" >Custom</div></div></td>
                    </tr>

                    <tr>
                        <td><div>Freichat Admin Password</div></td>
                        <td ><div><input id="am1" name="adminpass" size="30px" type="text" /></div></td>
                    </tr> 
                </tbody>

            </table>


            <div class="spaceout well" id="site_lang_used">
                <div class="spaceout">My website is running on:</div> 

                <label class="radio site_lang_radio">
                    <input type="radio" name="site_lang" value="php" checked />                     
                    PHP
                </label>
                <label class="radio site_lang_radio">
                    <input type="radio" name="site_lang" value="asp" />                     
                    ASP.NET
                </label>

            </div>
            <div class="alert alert-info" id="test_conn_status">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <div>After filling the above details hit submit</div> <!-- why waste a name on id -->
            </div>

            <button class="btn" id="db_submit">submit</button> 
            <span id="test_conn_img" class="test_conn_img hideme">
                <img src="../administrator/img/ajax-loaders/ajax-loader-1.gif" title="connecting....">
            </span>




            <div class="well hideme spaceout" id="tbl_gen">

                <div class="spaceout"> Please click on create tables button to generate and setup all required tables for freichat in your database</div>
                <button id="create_tables" class="btn btn-primary"><i class="icon-plus icon-white"></i> create tables</button>
                <span id="create_tables_img" class="hideme">
                    <img src="../administrator/img/ajax-loaders/ajax-loader-1.gif" title="creating tables....">
                </span>

            </div>


            <div class="well hideme" id="tbl_gen_s">
                <div class="alert alert-info" id="tbl_gen_info">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div>Creating required tables..... please wait. </div> <!-- why waste a name on id -->
                </div>

                <button class="btn btn-primary hideme" id="db_next"><i class="icon-arrow-right icon-white"></i> Next</button>


            </div>
            <div class="hideme" id="function_calls">FCS.smart()</div>

        </div>

    </div>






    <?php
    require 'footer.php';
















    