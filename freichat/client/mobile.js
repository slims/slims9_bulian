$jn=jQuery.noConflict(freidefines.jsconflicts);

$jn(document).ready(function(){  


var main_str =  "<div id='freichat_mobile_chat'>"+freidefines.TRANS.mobile_chat+"<span class='freichat_mobile_new_messages' id='freichat_mobile_new_messages'>0</span></div>";


var id = '<?php echo $cvs->session_id; ?>';
var xhash = '<?php echo preg_replace("/\?./","",$_SESSION[$uid."xhash"]); ?>';

var freichathtml = document.createElement("div");
    freichathtml.id = "freichathtml";
    freichathtml.innerHTML = main_str;
    document.body.appendChild(freichathtml);



$jn('#freichat_mobile_chat').click(function(){
    $jn("#freichat_mobile_new_messages").html(0).hide();
    window.open(freidefines.GEN.url+ 'client/chat.php?id='+id+'&xhash='+xhash+'&mobile=1');
});

$jn("#freichat_mobile_new_messages").hide();
var get_new_messages = function() {


var last_rec_time = 0;
var no_div = $jn("#freichat_mobile_new_messages");
var curr_width = "18px";

setInterval(function(){
    
    //check if i have a new message
    
    $jn.getJSON(freidefines.GEN.url+"server/freichat.php?freimode=get_new_messages_mobile",{
                xhash:freidefines.xhash,
                id:freidefines.GEN.getid,
                
                last_rec_time: last_rec_time
            //DB_obj:'<?php //echo json_encode($construct->db) ?>'

            },function(data){
                last_rec_time = data.last_rec_time;
            
                var no_of_messages = data.messages.length;
                var i;
                
                if(no_of_messages > 0){
                    var last_no = parseInt(no_div.html());
                    
                    var old_width = curr_width;
                    
                    if(last_no > 8) {
                        if(last_no > 98 && curr_width != "30px") {
                            curr_width = "30px";
                        }else{
                            
                            if(last_no < 98 && curr_width != "22px") {
                                curr_width = "22px";
                            }
                        }
                    }else{
                        if(curr_width != "18px") {
                            curr_width = "18px";
                        }
                    }
                    
                    if(curr_width != old_width) {
                        //changed
                        no_div.css("width",curr_width);                        
                    }
                                       
                    no_div.show().html(last_no+no_of_messages);
                }
                
                while(no_of_messages > 0) {
                    i = no_of_messages-1; //array index starts from 0
                         //for future           
                    no_of_messages--;
                }

            },'json');
    
},freidefines.SET.chatspeed);

};

get_new_messages();

});
