<?php
if (!isset($_SESSION['phplogin']) || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}
?>

<script>

    $(window).load(function() {

        //$('#paramsubmit2').button();
        //$('#tabs').tabs({selected:0})

    });


    function editroom(id, rnameid, rorderid, rpassid) {
        $('#editroom').modal('show');
        var name = $('#' + rnameid).html();
        var order = $('#' + rorderid).html();
        var pass = $('#'+ rpassid).html();
        
        if($.trim(pass) !== "") {
            $('#eroom_password').val(pass);
        }
        $('#eroom_name').val(name);
        $('#eroom_order').val(order);
        $('#eroom_id').val(id);
    }


    function confirm_delete(id) {
        var answer = confirm("Sure you want to Delete?")
        if (answer) {
            //alert("Deleted!");
            window.location = "admin.php?freiload=chatrooms/chatrooms&do=delete&id=" + id;

        }
        else {
            //alert("cancelled");
        }
    }

    function notify(text) {

        $(document).ready(function() {
            $.noty({text: text});
        });

    }

<?php
$construct = new FC_admin();
$db = $construct->db;


//-----create room    
if (isset($_GET['do']) && $_GET['do'] == 'create') {
    if (isset($_POST['room_name'])) {
        if ($_POST['room_name'] == "" || $_POST['room_order'] == "") {

            echo "notify('Error: Fields cannot be left empty!');";
        } else {
            $room_order = (int) $_POST['room_order'];
            $room_name = $_POST['room_name'];
            $room_password = $_POST['room_password'];

            if ($room_password == "") {
                $room_type = 0;
            } else {
                $room_type = 1;
            }

            $room_last_active = time();

            $room_name = htmlentities($room_name, ENT_QUOTES, "UTF-8");
            $qry = "INSERT INTO frei_rooms (room_author,room_name,room_type,room_password,room_created,room_last_active,room_order) VALUES(?,?,?,?,?,?,?)";
            $stmt = $db->prepare($qry);
            $stmt->execute(array("admin", $room_name, $room_type, $room_password,$room_last_active, $room_last_active, $room_order));
            echo "\n notify('Chatroom was successfully created'); ";
        }
    }
}

//---edit room
if (isset($_GET['do']) && $_GET['do'] == 'edit') {

    if (!isset($_POST['room_id'])) {
        echo "notify('Error: EDIT Fields cannot be left empty!');";
    } else {
        if (isset($_POST['room_name']) && isset($_POST['room_order'])) {
            if ($_POST['room_name'] == "" || $_POST['room_order'] == "") {
                echo "notify('Error: Fields cannot be left empty!');";
            } else {
                $order = (int) $_POST['room_order'];
                $id = (int) $_POST['room_id'];
                $room_name = $_POST['room_name'];
                $room_password = $_POST['room_password'];
                $room_name = htmlentities($room_name, ENT_QUOTES, "UTF-8");
                $qry = "UPDATE frei_rooms SET room_password=:pass,
                            room_name=:rname, room_order=:order WHERE id=:id";
                $stmt = $db->prepare($qry);
                $stmt->execute(array(":pass" => $room_password, ":rname" => $room_name, "order" => $order, ":id" => $id));
                echo "\n  notify('Your changes were saved successfully');";
            }
        }
    }
}

//---delete rooom
if (isset($_GET['do']) && $_GET['do'] == 'delete') {

    if (!isset($_GET['id'])) {

        echo "notify('Error: DEL Fields cannot be left empty!');";
    } else {
        $id = (int) $_GET['id'];
        //$room_name=str_replace("'"," ",$_POST['room_name']);
        if ($id == 1) {
            echo "notify('Error: DEL This chatroom cannot be deleted.');";
        } else {
            $qry = "DELETE FROM frei_rooms WHERE id=$id";
            $result = $db->query($qry);
            echo "\n//$qry";
            echo "\n notify('Chatroom was successfully Deleted.');";
        }
    }
}
?>
</script>


<div class="row-fluid sortable ui-sortable">		
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-plus"></i> Create chatroom</h2>
        </div>
        <div class="box-content">
            <form id="create_room_form" action="admin.php?freiload=chatrooms/chatrooms&do=create" method="post">

                <div class="control-group">
                    <label class="control-label" for="focusedInput"> Name</label>
                    <div class="controls">
                        <input type="text" name="room_name" placeholder="chatroom name" />
                    </div> <br/>
                    <label class="control-label" for="focusedInput"> Password (optional)</label>
                    <div class="controls">
                        <input type="text" name="room_password" placeholder="leave empty for public chatroom" />
                    </div> 

                </div>

                <div class="control-group">
                    <label class="control-label" for="focusedInput"> Order:</label>
                    <div class="controls">
                        <input id="room_order_input" type="number" name="room_order" placeholder="1" max="250" /></div>
                    <span class="muted"> Maximum value is 120 </span>
                </div>

                <button type="submit" id="paramsubmit2" class="btn" >Create room</button>
            </form>

        </div>
    </div><!--/span-->
</div>


<script>


    $("#create_room_form").submit(function() {

        if (parseInt($('#room_order_input').val()) > 120) {
            $('#room_order_input').val('120');
        } else if (parseInt($('#room_order_input').val()) < -120) {
            $('#room_order_input').val('-120');
        }

        $(this).submit();
        return false;
    });


</script>

<div class="row-fluid sortable ui-sortable">		
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-list"></i> Manage chatrooms</h2>
        </div>
        <div class="box-content">
            <table class="table table-striped table-bordered bootstrap-datatable datatable dataTable" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                <thead>
                    <tr role="row">
                        <th class="sorting_asc" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Username: activate to sort column descending">Room</th>
                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"  aria-label="Date registered: activate to sort column ascending">Password</th>
                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"  aria-label="Date registered: activate to sort column ascending">Order</th>
                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"  aria-label="Role: activate to sort column ascending">Edit</th>
                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"  aria-label="Status: activate to sort column ascending">Delete</th></tr>
                </thead>   

                <tbody role="alert" aria-live="polite" aria-relevant="all">

                    <?php
                    $cr = new FC_admin();

                    $result = $cr->db->query("SELECT * FROM frei_rooms");


                    $result = $result->fetchAll();

                    function create($result) {
                        $i = 0;
                        foreach ($result as $res) {
                            echo "<tr>";

                            echo "<td id='rname$i'>" . $res['room_name'] . "</td>";
                            echo "<td id='rpass$i'>" . $res['room_password'] . "</td>";

                            echo "<td id='rorder$i'>" . $res['room_order'] . "</td>";

                            echo "<td><a onmousedown='editroom(" . $res['id'] . ",\"rname" . $i . "\",\"rorder" . $i . "\",\"rpass" . $i . "\")' class='btn btn-info' href='#'>
                                    <i class='icon-edit icon-white'></i>  
                                    Edit                                            
                                </a></td>";

                            echo "<td><a onmousedown='confirm_delete(" . $res['id'] . ")' class='btn btn-danger' href='#'>
                                    <i class='icon-trash icon-white'></i> 
                                    Delete
                                </a></td>";

                            echo "</tr>";
                            $i++;
                        }
                    }

                    create($result);
                    ?>

                </tbody>
            </table>
        </div>
    </div><!--/span-->
</div>

<div id="editroom" class="modal hide fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>Edit room</h3>
    </div>
    <div class="modal-body">

        <form action="admin.php?freiload=chatrooms/chatrooms&do=edit" method="post">
            <div class="control-group">
                <label class="control-label" for="focusedInput"> Name</label>
                <div class="controls">
                    <input id="eroom_name" type="text" name="room_name" placeholder="1" /></div> 
            </div>
            <div class="control-group">
                <label class="control-label" for="focusedInput"> Password[Optional]</label>
                <div class="controls">
                    <input id="eroom_password" type="text" name="room_password" placeholder="leave empty for public chatroom" /></div> 
            </div>
            <div class="control-group">
                <label class="control-label" for="focusedInput"> Order</label>
                <div class="controls">
                    <input id="eroom_order" type="text" name="room_order" placeholder="1" /></div> 
            </div>
            <input type="hidden" name="room_id" id="eroom_id"/>
            <button id="paramsubmit3" class="btn btn-primary" type="submit"> Save Room</button>
            <button onclick="$('#editroom').modal('hide');
        return false" id="paramsubmit4" class="btn" > Cancel</button>

        </form>
    </div>    
</div>