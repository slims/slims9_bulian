<?php
//header is already included since called via AJAX

require 'header.php';

require 'integ/Custom.php';
$cls = new Custom();

if (@$_SERVER["HTTPS"] == "on") {
    $protocol = "https://";
} else {
    $protocol = "http://";
}
$address = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];


$path = str_replace("installation/smart_session.php", "", $address);
$cls->path_host = $path;
$output = $cls->info($path);

$path = str_replace($_SESSION["freichat_renamed"] . "/", "", $path);
?>


<div> 

    <div class="box span10 centerme" id="content">



        <div class="box-header well">
            <h2>freichat smart custom installation - step 2</h2>
        </div>

        <div class="box-content" >
            <div class="page-header">
                <h1>Website Integration</h1>
            </div>

            <h3 class="frei_ident_cookieless frei_ident_php_cookie hideme"> Copy-paste the following code after &lt;head&gt; tag in your index.php or your template/theme file </h3>
            <div class="muted"> Place it where you want freichat to be loaded </div>

            <div class="disabled frei_ident_cookieless hideme well">
                <textarea style="width:100%" rows="10" cols="50" onclick="this.focus();this.select()" readonly="readonly">
                    <?php echo $output['phpcode'] . $output['jscode'] . $output['csscode']; ?>
                </textarea>
            </div>

            <div class="disabled frei_ident_php_cookie hideme well">

                <textarea  style="width:100%" rows="10" cols="50" onclick="this.focus();this.select()" readonly="readonly">
                    <?php echo $output['php_cookie_code'] . $output['js_cookie_code'] . $output['css_cookie_code']; ?>
                </textarea>
            </div>

            <span class="frei_ident_csharp_cookie hideme">
                <div> Add below code in your Controllers </div>
                <div class="disabled well">

                    <textarea style="width:100%" rows="4" cols="50" onclick="this.focus();this.select()" readonly="readonly">
                        <?php echo $output['c#_cookie_code'];?>
                    </textarea>
                </div>

                <div> Add below code in your Views </div>
                <div class="disabled well">
                    <textarea style="width:100%" rows="4" cols="50" onclick="this.focus();this.select()" readonly="readonly">
                        <?php echo $output['js_cookie_code'] . $output['css_cookie_code']; ?>
                    </textarea>
                </div>

            </span>



            <button id="done_primary_paste" class="btn btn-primary"><i class="icon-ok icon-white"></i> Done</button>
            <span class="muted">After copy-pasting above code click done </span>


            <div style="margin-top:20px" class="well hideme" id="website_url">


                <label for="url">Enter your website url: </label><input id="website_url_text" type="text" value="<?php echo $path; ?>"/>

                <button style="display:block" id="url_submit" class="btn" >submit</button>
            </div>


            <div class="frame_load well hideme" id="frame_load">
                <iframe name="session_frame"  id="myframe" style="width:100%" height="400px" src="" seamless></iframe>
            </div>

            <div class="hideme well" id="canyousee">
                <h3 style="margin-bottom:10px;">Can you see FreiChat ?</h3>
                <button id="freichat_visible" style="margin-left:3%;" class="btn btn-primary"><i class="icon-ok icon-white"></i> Yes :)</button>
                <button id="freichat_invisible" class="btn btn-danger"><i class="icon-remove icon-white"></i> No :(</button>

            </div>

            <div id="no_visible_notice" class="well hideme" >

                <div class="alert alert-info" id="freichat_visibility">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div>checking for problems ......</div> <!-- why waste a name on id -->
                </div>

                <div class="muted"> If you have made any changes , you can reload the frame at anytime by clicking reload button below </div>
                <button style="margin-top:10px" id="frame_reload" class="btn">reload</button>
            </div>

            <div class="js_solve_div well hideme" id="js_solve_div">
                If you are unable to get freichat to work please generate an error report by clicking the following button and send it to us using the forums / submit a ticket
                <button style="display:block" id="generate_report" type="button" class="btn" >Generate report</button>
                <span class="muted hideme" id="report_progress">Generating report .... please wait.</span>

            </div>

        </div>




        <div class="hideme" id="function_calls">smart_session()</div>
    </div>

</div>


<script>
    
    //i wont be executed on ajax call  ;)
    document.location = 'smart.php';    
</script>


<?php
//footer is already included since called via AJAX

