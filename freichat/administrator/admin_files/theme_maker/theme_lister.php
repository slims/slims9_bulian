<?php
//if (!isset($_SESSION[$uid . 'curr_theme'])) {
//  $_SESSION[$uid . 'curr_theme'] = $freichat_theme;
//}
error_reporting(E_ALL);





require 'theme_maker.php';
?>   

<style>

    body {
        line-height: 32px;
    }
    @font-face {
        font-family: 'Exo';
        font-style: italic;
        font-weight: 600;
        src: local('Exo DemiBold Italic'), local('Exo-DemiBoldItalic'), url('admin_files/theme_maker/exo.woff') format('woff');
    }

    /*    @font-face {
            font-family: 'Sonsie One';
            font-style: normal;
            font-weight: 400;
            src: local('Sonsie One'), local('SonsieOne-Regular'), url('admin_files/theme_maker/sonsieone.woff') format('woff');
        }
    */

    input{
        /*font-family: 'Sonsie One', cursive;*/
        font-size: 18pt;
        text-align: center;
        width:300px;
        margin:12px;
    }

    #theme_list{
        /*border: 1px solid black;*/
        width:98%;
        cursor:pointer;
    }

    .theme_element:hover {
        background-color: #efefef;
    }

    .theme_element{
        text-align: left;
        height: 33px;
        width: 100%;
        border: 5px solid rgba(0, 0, 0, 0.05);
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        background: white;
        box-shadow: inset 1px 0px 6px 1px #bba;
        -moz-box-shadow: inset 1px 0px 6px 1px #bba;
        -o-box-shadow: inset 1px 0px 6px 1px #bba;
        -webkit-box-shadow: inset 1px 0px 6px 1px #bba;

    }



    .elem_theme_name{
        font-family: 'Exo', cursive;
        font-size: 18pt;
        text-align: center;


        width:76%;
        display: inline-block;
        height: 100%;
        float:left;
    }

    .theme_name_input {
        font-family: 'Exo', cursive;
        font-size: 18pt;
        text-align: center;
        width: 90%;
        margin: 0px;
        height: 80%;
    }

    .theme_name_button {
        float:right;
        font-size: 12px;
        margin:0px;
        height:100%;
        width:50px;
    }

    .elem_edit{
        width: 100px;
        display: inline-block;
        float: right;
        height: 100%;
    }

    .elem_edit input {
        margin:0px;
        width:100px;
        font-size:12px;
        height:100%;

    }
    .elem_delete{
        width: 40px;
        height: 100%;
        display: inline-block;

        float:right;
    }

    .elem_delete img {
        float: right;
        width: 30px;
        height: 30px;
        padding-top: 2px;
        padding-right: 6px;
    }

</style>

<script>

    function notify(text) {
        $(document).ready(function() {

            $.noty({text: text});
        });

    }

<?php
if (isset($_GET['do'])) {


    if ($_GET['do'] == 'create_theme' && isset($_POST['theme_name'])) {

        $theme_name = $_POST['theme_name'];
        $author_name = $_POST['author_name'];
        $_SESSION[$uid . 'curr_theme'] = 'basic';
        $thm = new theme_maker();

        $status = $thm->gateway($theme_name, $author_name);
        if ($status == 'exists') {
            echo "\n notify('Theme Already Exists');";
        } else if ($status == 'perms') {
            echo "\n notify('Permission Error: freichat/client/themes must be writable.');";
        }
    }
}
?>
</script>


<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-plus"></i> Create new theme</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">

                <div style="text-align:center;">

                    <form action="admin.php?freiload=theme_maker&do=create_theme" method="POST" id="myform" >    
                        <input id="theme_name" type="text" name="theme_name" value="ThemeName" onclick="this.value = ''" onblur="if (this.value == '')
            this.value = 'ThemeName'"/>
                        <input type="text" id="author_name" name="author_name" value="Author Name"  onclick="this.value = ''"  onblur="if (this.value == '')
            this.value = 'Author Name'"/>
                        <a id='ctb' onclick="submitform()" class="btn">Create theme</a>
                    </form>
                </div>

            </div>                   
        </div>
    </div><!--/span-->
</div>
<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-picture"></i> Themes list</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">

                <div style="text-align:center">
                    <div id="theme_list" style="text-align:center">

                        <?php
                        $thm = new theme_maker('../');
                        $themes = $thm->list_themes();
                        $a = '';
                        foreach ($themes as $theme) {
                            
                            if($theme == "smileys") continue;
                            
                            echo '<div class="theme_element">
            <span onclick="" id="theme_name_' . $theme . '" class="elem_theme_name">' . $theme . '</span>
<span onclick="delete_theme(\'' . $theme . '\')" class="elem_delete"><img src="admin_files/theme_maker/delete.jpg" height="33px" width="40px" alt="delete"/></span>
                      <span id="design_theme_' . $theme . '" class="elem_edit"><input class="btn" onclick="document.location = \'admin.php?freiload=theme_maker&load=theme_editor&theme_name=' . $theme . '\'" type="button" value="Design" /></span>            
<span class="elem_edit"><input class="btn" onclick="rename(\'' . $theme . '\')" type="button" value="Rename" /></span>
            
        </div>';
                        }

                        /*            <span onclick="" id="theme_name_' . $theme . '" class="elem_theme_name">' . $theme . '</span>
                          <span class="elem_edit">
                          <a onclick="rename(\'' . $theme . '\')" href="#" data-rel="tooltip" class="btn btn-warning" data-original-title="Rename theme">Rename</a>
                          </span>
                          <span id="design_theme_' . $theme . '" class="elem_edit">
                          <a onclick="document.location = \'admin.php?freiload=theme_maker&load=theme_editor&theme_name=' . $theme . '\'" href="#" data-rel="tooltip" class="btn btn-primary" data-original-title="Design theme">Design</a>
                          </span>
                          <span onclick="delete_theme(\'' . $theme . '\')" class="elem_delete">
                          <a href="#" data-rel="tooltip" class="btn btn-danger" data-original-title="Delete theme">Delete</a>
                          </span>
                         */
                        ?>
                    </div>
                </div>
            </div>                   
        </div>
    </div><!--/span-->
</div>
<script type="text/javascript">
    blocked_rename = false;
    last_element_renamed = false;


    function delete_theme(id) {

        if (id == 'basic') {
            alert('cannot delete system themes');
            return;
        }

        var rep = confirm("Do you really want to delete this theme ?");

        if (rep == true) {
            $.getJSON("admin_files/theme_maker/theme_maker.php?action=delete_theme", {
                theme_name: id
            },
            function(data) {

                if (data == 'success') {
                    // FreiChat.notify('theme '+theme+' deleted successfully');
                    window.location.reload(true);
                } else if (data == 'perms') {
                    alert('Permission Error: freichat/client/themes must be writable');
                }

            }, 'json');

        }
    }


    function submitform() {

        var theme_name = $('#theme_name').val();
        if (theme_name.indexOf(" ") != -1) {
            alert("You cannot have space(s) in your theme name");
            return;
        }

        if (theme_name.match(/[\W_]/)) {
            alert('Please enter only alphanumeric characters');
            return;
        }

        $('#theme_name').val($.trim(theme_name));
        var author_name = $('#author_name').val();
        $('#author_name').val($.trim(author_name));
        $('#myform').submit();
    }

    function rename(id) {
        if (id == 'basic') {
            $.noty({text: "Cannot rename default theme"});
            return;
        }

        if (last_element_renamed != false) {//alert(last_element_renamed);
            back_to_def();
        }

        //if(blocked_rename == false) {
        var div = $('#theme_name_' + id);
        var value = div.html();
        last_element_renamed = id;
        last_element_value = value;
        div.html('<input class="theme_name_input" id="theme_name_input_' + id + '" "type="text" id=\'theme_name_' + id + '\' value="' + value + '" /><input onclick="rename_submit(\'' + id + '\')" class="theme_name_button" type="button" value="set" /><input onclick="back_to_def()" class="theme_name_button" type="button" value="X" />');
        $("#theme_name_input_" + id).focus();

        //}
        //blocked_rename = true;
    }

    function rename_submit(id) {
        var new_name = $('#theme_name_input_' + id).val();
        if (new_name.indexOf(' ') != -1) {
            alert('You cannot have spaces in your theme name');
            return;
        }

        new_name = $.trim(new_name);
        if (new_name == '') {
            alert('You cannot leave the field empty');
            return;
        }

        if (new_name.match(/[\W_]/)) {
            alert('Please enter only alphanumeric characters');
            return;
        }

        $.getJSON("admin_files/theme_maker/theme_maker.php?action=rename_theme",
                {
                    old_name: last_element_value,
                    theme_name: new_name
                },
        function(data) {
            if (data['theme_name'] == 'exists') {
                alert("a theme with this name already exists\nplease choose a different name");
            } else if (data['theme_name'] == 'perms') {
                alert("Could not save theme \n change permissions of directory ~/freichat/client/themes");
            } else {//alert(new_name+id);
                $('#design_theme_' + last_element_renamed).html('<input onclick="document.location = \'admin.php?freiload=theme_maker&load=theme_editor&theme_name=' + new_name + '\'" type="button" value="Design" />');
                last_element_renamed = new_name;
                last_element_value = new_name;
                back_to_def(id, new_name);

            }
        }, 'json');
    }

    function back_to_def(id, value) {

        if (id == undefined)
            id = last_element_renamed;
        if (value == undefined)
            value = last_element_value;
        $('#theme_name_' + id).html(value);
    }

    //$('#ctb').button();     

</script>    