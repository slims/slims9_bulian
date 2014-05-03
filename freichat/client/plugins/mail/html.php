<!DOCTYPE html>
<html>
    <head>
        <title>
            Send chat conversation via mail
        </title>
        <script src="../../jquery/js/jquery.1.8.3.js"></script>
        <script src="../lib/js/bootstrap.min.js"></script>
        <script src="../lib/js/bootstrap-fileupload.min.js"></script>

        <link href="../lib/css/bootstrap.min.css" rel="stylesheet" />
        <link href="../lib/css/bootstrap-fileupload.min.css" rel="stylesheet" />
        <style>
            input {
                width: 96%;
            }

            .control-group {
                border: 1px dashed grey;
                padding: 5px;
                background: #eee;
            }
        </style>
    </head>
    <body>
        <br/>
        <form name="upload" action="sendmail.php" method="post">

            <div class="control-group">
                <label class="control-label" for="inputSubject">Subject</label>
                <div class="controls">
                    <input type="text" name="subject" id="subject" placeholder="enter your subject">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputMail">Email address</label>
                <div class="controls">
                    <input type="text" name="mailto" id="mailto" placeholder="enter receiver's email address">
                </div>
            </div>

            <input id ="fromid" type="hidden" name="fromid"/>
            <input id="fromname" type="hidden" name="fromname"/>
            <input id="toid" type="hidden" name="toid"/>
            <input id="toname" type="hidden" name="toname"/>
            <input id="mode" type="hidden" name="mode" />

            <input  class="btn btn-block btn-large" type="submit" name="submit" value="Send" />
        </form>


    </body>

</html>
<script>
    function freiVal(name, value)
    {
        var element = document.getElementById(name);

        if (element != null)
        {
            element.value = value;
        }
        else
        {
            alert("element does not exists");
        }
    }

    if (opener.FreiChat.is_chatroom) {
        freiVal("subject", "Conversation with " + opener.FreiChat.touser);
        freiVal("mode", "private_chat");
    } else {
        freiVal("subject", "chatroom onversation");
        freiVal("mode", "chatroom");
    }
    freiVal("toid", opener.FreiChat.toid);
    freiVal("fromid", opener.freidefines.reidfrom);
    freiVal("toname", opener.FreiChat.touser);
    freiVal("fromname", opener.freidefines.fromname);
</script>