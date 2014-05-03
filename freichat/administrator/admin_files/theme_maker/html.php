<!DOCTYPE html>
<html>
    <title>
        Replace image
    </title>
    <body>
        <form name="upload" action="upload.php" method="post" enctype="multipart/form-data">
            <label for="file">Select image to replace:</label><br/>
            <span id ="oldimage"></span>
            <input id ="originalfilename" type="hidden" name="originalfilename"/>
            <input id ="imgid" type="hidden" name="imgid"/>
            <input id ="imgid2" type="hidden" name="imgid2"/>
            <input id ="type" type="hidden" name="type"/>
            <input id ="variable_php" type="hidden" name="variable_php"/>
            <input id ="variable_js" type="hidden" name="variable_js"/>



            



            <input type="file" name="file" id="file" />
            <br /><br/>
            <input  type="submit" name="submit" value="Replace" />
        </form>
    </body>
</html>
<script>
    
    /*var str = "<img src='../../../client/jquery/freichat_themes/"+opener.freidefines.GEN.curr_theme+"/"+opener.FreiChat.name+"' /> ";
    
    var element = document.getElementById('oldimage');
    element.innerHTML = str;*/
    
    function freiVal(name,value)
    {
        var element = document.getElementById(name);

        if(element != null)
        {
            element.value=value;
        }
        else
        {
            alert("element does not exists");
        }
    }

    freiVal("originalfilename",opener.FreiChat.name);
    freiVal("imgid",opener.FreiChat.id);
    freiVal("imgid2",opener.FreiChat.id2);
    freiVal("type",opener.FreiChat.type);
    freiVal("variable_php",opener.FreiChat.variable_php);
    freiVal("variable_js",opener.FreiChat.variable_js);



</script>
