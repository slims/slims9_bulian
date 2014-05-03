<?php
session_start();

/* Make me secure */


if (!isset($_GET[$_SESSION['index']])) {
    header("Location:index.php");
    exit;
}

/* Now i am secure */
$_SESSION['FREIX'] = 'authenticated';
$_SESSION['nocheck'] = true;

require "header.php";
?>
<br/>
<br/>
<br/>
<div style="font-family: 'Sonsie One', cursive;font-size: 18pt;text-align: center">
    <p>Choose Integration Type</p>
</div>

<br/>
<br/>
<br/>
<div style="text-align: center">
    <form name="cms" action="info.php" method="POST" id="sform">
        <select id="CMS" name='cms' id="cms" style="font-family: 'Changa One', cursive;font-size: 16px">

            <?php
            //Used PHP to sort by alphanumeric
            //DZCP driver <!--Mod by: Richy => www.my-starmedia.de-->

            $name = "ccms";
            $options = "<option selected='selected'></option>";
            $cms_s = array(
                array(
                    "value" => "Joomla",
                    "text" => "Joomla",
                ),
                array(
                    "value" => "JCB",
                    "text" => "Joomla with CB",
                ),
                array(
                    "value" => "JSocial",
                    "text" => "Joomla with JomSocial",
                ),
                array(
                    "value" => "CBE",
                    "text" => "Joomla with CBE",
                ),
                array(
                    "value" => "Drupal",
                    "text" => "Drupal",
                ),
                array(
                    "value" => "Elgg",
                    "text" => "Elgg",
                ),
                array(
                    "value" => "WordPress",
                    "text" => "WordPress",
                ),
                array(
                    "value" => "Phpbb",
                    "text" => "Phpbb",
                ),
                array(
                    "value" => "Sugarcrm",
                    "text" => "Sugarcrm",
                ),
                array(
                    "value" => "Phpvms",
                    "text" => "PhpVMS",
                ),
                array(
                    "value" => "Phpfox",
                    "text" => "PhpFox",
                ),
                array(
                    "value" => "Phpfusion",
                    "text" => "PhpFusion",
                ),
                array(
                    "value" => "DZCP",
                    "text" => "DZCP",
                )/* ,
                  array(
                  "value" => "EE",
                  "text" => "Expression Engine",
                  ) */,
                array(
                    "value" => "Custom",
                    "text" => "Customized",
                ),
                array(
                    "value" => "SMF",
                    "text" => "Simple Machines Forum",
                ),
                array(
                    "value" => "Oxwall",
                    "text" => "Oxwall",
                ),
                array(
                    "value" => "se4",
                    "text" => "Social Engine 4"
                ),
                array(
                    "value" => "Jcow",
                    "text" => "Jcow"
                ),
                
                array(
                    "value" => "Etano",
                    "text" => "Etano"
                )

            );

            function cmp($a, $b) {
                return strcmp($a['text'], $b['text']);
            }

            usort($cms_s, "cmp");

            foreach ($cms_s as $cms) {
                $options .= "<option name='" . $name . "' value='" . $cms['value'] . "' id='" . $cms['value'] . "_option'>" . $cms['text'] . "</option>";
            }

            echo $options;
            ?>
        </select>
        <br/>
        <br/>
        <div id="CBE_ver_div" class="hideme" style="font-family: 'Changa One', cursive;font-size: 16px">
            Please select the appropriate version of CBE installed<br/><br/>
            <select name="CBE_ver" style="font-family: 'Changa One', cursive;font-size: 16px">
                <option name="CBE_ver_" value="1"> CBE 1.5.x </option>
                <option name="CBE_ver_" value="2"> CBE 2.5.x </option>
            </select>
        </div>
        <div id="SMF_ver_div" class="hideme" style="font-family: 'Changa One', cursive;font-size: 16px">
            Please select the appropriate version of SMF installed<br/><br/>
            <select name="SMF_ver" style="font-family: 'Changa One', cursive;font-size: 16px">
                <option name="SMF_ver_" value="1"> SMF 1.x </option>
                <option name="SMF_ver_" value="2"> SMF 2.x </option>
            </select>
        </div>

        <br/>
        <br/>
        <br/>
        <br/>
        <p>
            <a href="JavaScript:void(0)" class="nextbutton" onclick="submit_form()">Next</a>
        </p>
</div>

<script type="text/javascript">
   
    $(document).ready(function(){
 
        var div;

        $('#CMS').change(function(){//alert("f");
            
            var sub_cms = ["CBE","SMF"];
            var len = sub_cms.length;
            for(var i=0; i<len; i++) {
                div = $('#'+sub_cms[i]+"_ver_div");

                if($(this).find(":selected").val() == sub_cms[i]) {
                    div.show();
                }else{
                    div.hide();
                }                
            }
            
          
        });
      
    });
   
    function submit_form(){
       
        if($('#cms option:selected').val()!=""){
           
            $('#sform').submit();
           
        }
        else{
            alert('Please Select an integration type!');
        }
    }
    
</script>


</form>



<?php
require 'footer.php';
?>