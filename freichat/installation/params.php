<?php
require 'header.php';

class Configure {

    public function getCONF() {


        $cname = $_SESSION['cms'];
        require 'integ/' . $_SESSION['cms'] . '.php';
        $cls = new $cname();
        $config = $cls->get_config();

        return $config;
    }

    public function run_me() {
        $conf = $this->getCONF();
        return $conf;
    }

}

$configure = new Configure();
$conf = $configure->run_me();
?>
<style type="text/css">
    table.center {
        width:70%; 
        margin-left:15%; 
        margin-right:15%;
    }
    td{
        text-align:right;  
    }

    .zentrum{
        text-align: center;
    }


    input[type="text"] {
        width: 84%;
        padding-left: 4px;
        line-height: 12px;
        height: 30px;
    }

</style>

<?php
$p_only = false;
$chng_name = "";
$sentence = 'Choose a Password';
$disp = "";

if ($_SESSION['cms'] == 'Custom') {

    $p_only = false;
    $sentence = 'Fill in the blanks';
    $chng_name = "";
    $disp = 'block';
} else {
    $chng_name = "name_changed";
    $p_only = true;
    $disp = 'none';
}
?>

<div id='Finfo' style="text-align: center">
    <br/>
    <p id="sentence" style="font-family: 'Sonsie One', cursive;font-size: 18pt;"><?php echo $sentence; ?></p>
    <form name="input" action="install.php" method="POST" id="paramform">
        <p >
            <br/>
            <br/>
            <br/>
        <div id="db_create_no" class="hideme spaceout">
            <b>Please do not create a new database for freichat</b> Enter the database name that is currently used by 
            your website to store all the details of your users

        </div>

        <table border="0" id="tble" class="center table table-striped table-bordered" style="display:<?php echo $disp; ?>">	 


            <tbody>
                <tr>
                    <td>DB Host </td>
                    <td class="zentrum"><input name="host" id="host" size="30px" type="text" value="" /></td>
                </tr>
            <span class="port_hide" >
                <tr>
                    <td>DB Port </td>
                    <td class="zentrum"><input name="port" id="v_port" size="30px" type="text" value="" /></td>
                </tr>
            </span>

            <tr>
                <td>DB Username </td>
                <td class="zentrum"><input name="muser" id="muser" size="30px" type="text" value="" /></td>
            </tr>

            <tr>
                <td>DB Password </td>
                <td class="zentrum"><input name="mpass" id="mpass" size="30px" type="text" value="" /></td>
            </tr>

            <tr>
                <td>DB Database Name </td>
                <td class="zentrum"><input name="dbname" id="dbname" size="30px" type="text" value="" /></td>
            </tr>

            <tr>
                <td>Table Prefix </td>
                <td class="zentrum"><input name="dbprefix" id="dbprefix" size="30px" type="text" value="" /></td>
            </tr>

            <tr>
                <td>Integrates With </td>
                <td class="zentrum"><input name="driver" size="30px" type="text" value="<?php echo $_SESSION['cms']; ?>" /></td>
            </tr>

            </tbody>

        </table>
        <input id="am2" name="adminpass" style="padding: 17px;line-height: 12px;font-family: \'Exo\', sans-serif;font-weight:600 ;font-style:italic;width:500px;font-size:18pt;text-align:center" size="30px" type="text" value="" />
        <input name="freichat_to_path" size="30px" type="hidden" value="<?php echo $_SESSION["freichat_renamed"]; ?>" />

        </p>
        <br/>
        <br/>
        <a href="JavaScript:void(0)" class="nextbutton" onclick="to_install()">Proceed</a>
    </form>
</div>
<script type="text/javascript">
    
    $('#port_hide').hide();
    
    function to_install(){
        var pass = $.trim($('#am2').val()),passed=false;
                
                
        if(pass == "") {
            $.noty({text:"Administrator password cannot be left empty"});
        }
        else if(pass == "adminpass") {
            $.noty({text:"Administrator password cannot be 'adminpass'"});                    
        }
        else if(pass.length <= 4) {
            $.noty({text:"administrator password length must be greater than 4"});
        }else{
            passed = true;
        }
                
        if(!passed) {
            return false;
        }
        
        var db_details;

        if($("#tble").is(":visible")) {
            db_details = {
                host: $('#host').val(),
                dbname: $('#dbname').val(),
                muser: $('#muser').val(),
                mpass: $('#mpass').val(),
                port: $('#v_port').val(), //blank by default
                db_prefix: $('#dbprefix').val(),
                admin_pass:pass
            }
        }else{
            db_details = {
                host:'<?php echo $conf[0]; ?>',
                dbname:'<?php echo $conf[3]; ?>',
                muser:'<?php echo $conf[1]; ?>',
                mpass:'<?php echo htmlentities($conf[2], ENT_QUOTES); ?>',
                port:$('#v_port').val(), //blank by default
                db_prefix:'<?php echo $conf[4]; ?>',
                admin_pass:pass                
            }
        }

        db_details.cms = "<?php echo $_SESSION['cms']; ?>";

        $.post('testconnection.php',
        
        db_details
        
        ,function(data){



            if(data=='works'){
 
                $('#paramform').submit();
            }
            else{
                $.noty({text: "ERROR: "+data});
                $('#am2').hide();
                $('#am1').attr('name','adminpass');
                $('#sentence').html('is this correct?');
                $('#tble').show();
                $('#port_hide').show();
                $('#db_create_no').show();
            }
            
        });
    
        // 
    
    }

</script>
<?php
require "footer.php";
?>
