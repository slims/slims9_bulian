<!---<script type="text/javascript" src="../client/jquery/js/jquery.1.7.1.js"></script>

<script type="text/javascript" src="../client/jquery/js/jquery-ui.js"></script>


<link rel="stylesheet" href="../client/jquery/js/jquery-ui.css">
--->

<script> 
    function helpme2()
    {
        var str="Template Patch(Beta!)\n\nThis option is exclusively made for those templates that change the HTML of freichat making it come behind the modules or changing the original colours of freichat and creating other css related problems\n\nIf you enable this option it will add freichat css to your template making it a part of your template.\n\nIf you are not getting the desired results Go to your template folder and change the file permissions to read and write (0777) Or post the problem in the codologic forums";
        alert(str);
    }
    function helpme1()
    {
        var str="This option removes conflicts with other extensions using jquery.\n\nThis option should be set to yes only and only if freichat is not working because of some external jquery conflicts. \n\nUse this option only if freichat doesnt work normally.";
        alert(str);
    }

    $(window).load(function(){
        //$('#paramsubmit1').button();
        //$('#paramsubmit2').button();
        //$('#tabs').tabs({selected:0})
    });
</script>
