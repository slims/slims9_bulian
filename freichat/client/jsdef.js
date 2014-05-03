function Get_Cookie(b){if("undefined"!==typeof Storage)return sessionStorage.getItem(b);for(var c=document.cookie.split(";"),a="",d="",f="",e=0;e<c.length;e++)if(a=c[e].split("="),d=a[0].replace(/^\s+|\s+$/g,""),d==b)return 1<a.length&&(f=unescape(a[1].replace(/^\s+|\s+$/g,""))),f;return!1}
function Set_Cookie(b,c){if("undefined"!==typeof Storage)sessionStorage.setItem(b,c);else{var a=0,d=new Date;d.setTime(d.getTime());a&&(a*=864E5);d=new Date(d.getTime()+a);document.cookie=b+"="+escape(c)+(a?";expires="+d.toGMTString():"")+";path=/"}}function Delete_Cookie(b,c,a){Get_Cookie(b)&&(document.cookie=b+"="+(c?";path="+c:"")+(a?";domain="+a:"")+";expires=Thu, 01-Jan-1970 00:00:01 GMT")};

if(!Get_Cookie('frei_time')){
    Set_Cookie('frei_time',"<?php echo time(); ?>");
}
if(!Get_Cookie('frei_custom_mesg')){
    Set_Cookie('frei_custom_mesg',"<?php echo $frei_trans['default_status']; ?>");
}

if(!Get_Cookie('frei_sound')){
    Set_Cookie('frei_sound',"on");
}


if(!Get_Cookie('selected_chatroom')) {
   Set_Cookie('selected_chatroom', "-1");
}
    
if(!Get_Cookie('selected_chatroom_color')) {
   Set_Cookie('selected_chatroom_color', "grey");    
}    

/*-----------------------Definitions--------------------------------------------*/
freidefines = {
     
    // GEN => GENERAL   CONFIGURATION
    GEN : {
        is_guest : '<?php echo $_SESSION[$uid."is_guest"]; ?>',//
        url:'<?php echo $url; ?>',
        ses_status:'<?php echo $_SESSION[$uid."freistatus"]; ?>',//
        reidfrom:'<?php echo $id; ?>',//
        getid:'<?php echo $cvs->session_id; ?>',
        fromname:'<?php echo $pfromname; ?>',
        custom_mesg:Get_Cookie('frei_custom_mesg'),
        time:Get_Cookie('frei_time'),
        fromid:'<?php echo $_SESSION[$uid . "usr_ses_id"]; ?>',
        referer:window.location.href,
        rtl:'<?php echo $_SESSION[$uid."rtl"]; ?>',
        content_height:'<?php echo $cvs->content_height; ?>',
        user_defined_chatbox_content_status:'<?php echo $cvs->user_defined_chatbox_content_status; ?>',
        BOOT:'<?php echo $cvs->BOOT; ?>',
        force_load_jquery: '<?php echo $cvs->force_load_jquery; ?>',
        custom_error_handling: '<?php echo $cvs->custom_error_handling; ?>'
    },
   
    // SET => GENERAL   SETTINGS 
    SET : {
        theme:'<?php echo $cvs->color; ?>',
        fonload:'<?php echo $cvs->load; ?>',
        chatspeed:'<?php echo $cvs->chatspeed; ?>',
        draggable:'<?php echo $cvs->draggable; ?>',
        fxval:'<?php echo $cvs->fxval; ?>', //Jquery Effects
        mesgSendSpeed:'<?php echo $cvs->msgSendSpeed; ?>',
        addedoptions_visibility:'<?php echo $cvs->addedoptions_visibility; ?>',
        show_avatar: '<?php echo $cvs->show_avatar; ?>',
        
        allow_guest_name_change: '<?php echo $cvs->allow_guest_name_change; ?>'
    },  
    
    STATUS : {
        
        IMG : {
            online:'<?php echo $frei_trans["go_online"]; ?>',
            offline:'<?php echo $frei_trans["go_offline"]; ?>',
            busy:'<?php echo $frei_trans["go_busy"]; ?>',
            invisible:'<?php echo $frei_trans["go_invisible"]; ?>' 
        },
        
        TEXT : {
            online:'<?php echo $frei_trans["status_txt_online"]; ?>',
            busy:'<?php echo $frei_trans["status_txt_busy"]; ?>',
            invisible:'<?php echo $frei_trans["status_txt_invisible"]; ?>',
            offline:'<?php echo $frei_trans["status_txt_offline"]; ?>'         
        }        
    },
    
    video_config : {
        api_key: '<?php echo "20319841"; ?>'
    },
 
    TRANS : {
        chat_message_me:'<?php echo $frei_trans["chat_message_me"]; ?>',
        chat_status:'<?php echo $frei_trans["chat_status"]; ?>',
        rtl:'<?php echo $frei_trans["rtl"]; ?> ',
        ban_mesg:'<?php echo $frei_trans["ban_mesg"]; ?>',
        chatroom_label:'<?php echo $frei_trans["chatroom_label"];?>',
        no_search_results:'<?php echo $frei_trans["no_search_results"]; ?>',
        custom_mesg_update:'<?php echo $frei_trans["custom_mesg_update"]; ?>',
        g_prefix:'<?php echo $frei_trans["g_prefix"]; ?>',
        
        chatroom_tab_users: '<?php echo $frei_trans["chatroom_tab_users"]; ?>',
        chatroom_tab_rooms: '<?php echo $frei_trans["chatroom_tab_rooms"]; ?>',
        chatroom_hide: '<?php echo $frei_trans["chatroom_hide"]; ?>',
        mobile_chat: '<?php echo $frei_trans["mobile_chat"]; ?>',
        
        vid_req_sent: '<?php echo $frei_trans["vid_req_sent"]; ?>',
        chatroom_lobby: '<?php echo $frei_trans["chatroom_lobby"]; ?>',
        new_chatroom_message: '<?php echo $frei_trans["new_chatroom_message"]; ?>',
        chatroom_creator_new: '<?php echo $frei_trans["chatroom_creator_new"]; ?>',
        chatroom_creator_pass: '<?php echo $frei_trans["chatroom_creator_pass"]; ?>',      
        chatroom_creator_paswd: '<?php echo $frei_trans["chatroom_creator_paswd"]; ?>',
        chatroom_creator: '<?php echo $frei_trans["chatroom_creator"]; ?>',
        chatroom_creator_exists: '<?php echo $frei_trans["chatroom_creator_exists"]; ?>',
        cancel: '<?php echo $frei_trans["cancel"]; ?>',
        create_chatroom_title: '<?php echo $frei_trans["create_chatroom_title"]; ?>',
        custom_guest_name_exists: '<?php echo $frei_trans["custom_guest_name_exists"]; ?>',
        chatroom_tools_toggle: '<?php echo $frei_trans["chatroom_tools_toggle"]; ?>'
    },
    
    lockedimg: '<?php echo $lockedimg; ?>',    
    wrenchimg: '<?php echo $wrenchimg; ?>',
    smiley_chatroomimg:'<?php echo $smiley_chatroomimg; ?>',
    rtlimg_enabled:'<?php echo $rtlimg_enabled; ?>',
    rtlimg_disabled:'<?php echo $rtlimg_disabled; ?>',
    gchatimg:'<?php echo $gchatimg; ?>',
    mailimg: '<?php echo $mailimg; ?>',
    saveimg: '<?php echo $saveimg; ?>',
    videoimg: '<?php echo $videoimg;?>',
    smileyimg: '<?php echo $smileyimg; ?>',
    arrowimg: '<?php echo $arrowimg; ?>',
    newtopimg: '<?php echo $newtopimg; ?>',
    btopimg:'<?php echo $btopimg; ?>',
    notransimg: '<?php echo $notransimg; ?>',
    translateimg:'<?php echo $translateimg; ?>',
    uploadimg:'<?php echo $uploadimg; ?>',
    deleteimg:'<?php echo $deleteimg; ?>',
    minimg:'<?php echo $minimg; ?>',
    maximg:'<?php echo $maximg; ?>',
    closeimg:'<?php echo $closeimg; ?>',
    logoutimg:'<?php echo $logoutimg; ?>',
    onlineimg:'<?php echo $onlineimg; ?>',
    busyimg:'<?php echo $busyimg; ?>',
    invisibleimg:'<?php echo $invisibleimg; ?>',
    restoreimg:'<?php echo $restoreimg; ?>',
    offlineimg:'<?php echo $offlineimg; ?>',
    offline:'<?php echo $offline; ?>',
    optimg:'<?php echo $optimg; ?>',
    toolimg:'<?php echo $toolimg; ?>',
    chatroomimg:'<?php echo $chatroomimg; ?>',
    jquery_theme:'<?php echo $jquery_theme; ?>',
    fnopermsht:'<?php echo $fnopermsht; ?>',     //Height When user has no permissions
    fnoonlineht:'<?php echo $fnoonlineht; ?>',   //Height When No one is online
    fone_onlineht:'<?php echo $fone_onlineht; ?>',    //Height When one user online
    fmaxht:'<?php echo $fmaxht; ?>',      //Height when more than one user

    smileys:JSON.parse('<?php echo $smileys; ?>'),
    thememaker:false,
 
    mobile:'<?php echo $mobile; ?>',
 
    chatHistoryDeleted:'<?php echo $frei_trans["chatHistoryDeleted"]; ?>',
    chatHistoryNotFound:'<?php echo $frei_trans["chatHistoryNotFound"]; ?>',
    cb_head:'<?php echo $frei_trans["cb_head"]; ?>',
    pwdby:'<?php echo $frei_trans["pwdby"]; ?>',
    nopermsmesg:'<?php echo $frei_trans["noperms"]; ?>',
    nolinemesg:'<?php echo $frei_trans["noline"]; ?>',
    chatroom_nolinemesg:'<?php echo $frei_trans["chatroom_noline"]; ?>',
    newmesg:'<?php echo $frei_trans["newmesg"]; ?>',
    onfoffline:'<?php echo $frei_trans["on_offline"]; ?>',
    restore_drag_pos:'<?php echo $frei_trans["restore_drag_pos"]; ?>',
    status_txt:'<?php echo $frei_trans["status_txt"]; ?>',
    opt_txt:'<?php echo $frei_trans["opt_txt"]; ?>',
    onOfflinemesg:'<?php echo $frei_trans["onOfflinemesg"]; ?>',

    plugin_trans_disable:'<?php echo $frei_trans["plugin_transdisable"]; ?>',
    plugin_trans_orig:'<?php echo $frei_trans["plugin_trans_orig"]; ?>',

    titles_translate:'<?php echo $frei_trans["titles_translate"]; ?>',
    titles_upload:'<?php echo $frei_trans["titles_upload"]; ?>',
    titles_mail: '<?php echo $frei_trans["titles_mail"]; ?>',
    titles_smiley: '<?php echo $frei_trans["titles_smiley"]; ?>',
    titles_clrcht: '<?php echo $frei_trans["titles_clrcht"]; ?>',
    titles_save: '<?php echo $frei_trans["titles_save"]; ?>',
    titles_videochat: '<?php echo $frei_trans["titles_videochat"]; ?>',

    status_online:'<?php echo $frei_trans["status_online"]; ?>',
    status_busy:'<?php echo $frei_trans["status_busy"]; ?>',
    status_invisible:'<?php echo $frei_trans["status_invisible"]; ?>',
    status_offline:'<?php echo $frei_trans["status_offline"]; ?>',
    default_status:'<?php echo $frei_trans["default_status"]; ?>',

    set_custom_mesg:'<?php echo $frei_trans["set_custom_mesg"]; ?>',
    chat_room_title: '<?php echo $frei_trans["chat_room_title"]; ?>',

    PLUGINS : {
      
        show_file_send:'<?php echo $cvs->show_file_sending_plugin; ?>',
        showtranslate:'<?php echo "disabled"; ?>',
        showsmiley:'<?php echo $cvs->show_smiley_plugin; ?>',
        showsave:'<?php echo $cvs->show_save_plugin; ?>',
        showmail:'<?php echo $cvs->show_mail_plugin; ?>',
        showchatroom:'<?php echo $show_chatroom_plugin; ?>',
        showvideochat:'<?php echo $cvs->show_videochat_plugin; ?>',
        
        chatroom_location: '<?php echo $cvs->chatroom_location; ?>',
        chatroom_autoclose: '<?php echo $cvs->chatroom_autoclose; ?>',
        chatroom_offset: '<?php echo $cvs->chatroom_offset; ?>',
        chatroom_label_offset: '<?php echo $cvs->chatroom_label_offset; ?>',
        chatroom_rotate: '<?php echo $chatroom_rotate; ?>',
        chatroom_override_positions: '<?php echo $cvs->chatroom_override_positions; ?>',
        
        chat_time_shown_always: '<?php echo $cvs->chat_time_shown_always; ?>'
    },  



    JSdebug:'<?php echo $cvs->JSdebug; ?>',
    playsound:'<?php echo $cvs->playsound ?>',
    busy_timeOut:'<?php echo $cvs->busy_timeOut; ?>',
    offline_timeOut:'<?php echo $cvs->offline_timeOut; ?>',


    xhash: '<?php echo preg_replace("/\?./","",$_SESSION[$uid."xhash"]); ?>',
    jconflicts:'<?php echo $cvs->conflict; ?>'
};

freidefines.ACL = JSON.parse('<?php echo json_encode($ACL); ?>');


//COMM_15_DAY_NOTIFY_2




/*----------------------THE-PHP-JS-Bridge---------------------------------------*/



//COMM_EVERY_TIME_CHECK


if(freidefines.PLUGINS.chatroom_override_positions == 'yes') {
    //positions are taken from the theme instead from the backend
    
    freidefines.PLUGINS.chatroom_label_offset = '<?php echo $chatroom_position; ?>';
    freidefines.PLUGINS.chatroom_offset = '<?php echo $chatroom_offset; ?>';
}



/* THE main FreiChatX INIT part */


var X_init = false;
if(typeof FreiChat != "undefined")
{
    X_init = true;
}


var FreiChat = {
    oldtitle:document.title,
    loop:0,
    last_chatroom_msg_id:null,
    last_chatroom_usr_id:null,
    last_chatroom_msg_type:[], //[BUBBLE !!]TRUE ->RIGHT FALSE ->LEFT
    ses_status:freidefines.GEN.ses_status,
    time:0,
    chatroom_mesg_time:0,
    freistatus:null,
    ostatus:null,
    box_count:0,
    box_crt:[],
    box_crt_id:[],
    room_array:[],
    windowFocus:false,
    debug:freidefines.JSdebug, //Set to true to debug with firebug , set to false(default) when over
    cnt:0,
    inact_time:0,      //initial Inactivity time
    busy_timeOut:freidefines.busy_timeOut,  // In seconds
    offline_timeOut:freidefines.offline_timeOut, //In seconds
    inactive:false,  //initially not inactive
    onloadActive:false,
    clrchtids:[],
    bulkmesg:[],
    isOlduser:null,
    load_chatroom_complete:false,
    //Ttext:null,
    temporary_status:0,
    unique:0,
    timer:null,
    change_titletimer:null,
    first:false,
    RequestCompleted_get_members:true,
    RequestCompleted_send_messages:true,
    RequestCompleted_isset_mesg:true,
    SendMesgTimeOut:0,
    passBYpost:false,
    custom_mesg:'i am null',
    in_room:Get_Cookie('selected_chatroom'),
    chatroom_users : [],
    title: 'General Talk',
    bulkmesg_chatroom:[],
    height:20,
    chatroom_changed:false,
    first_message:true,
    last_chatmessage_usr_id:{},
    msg_access:true,
    long_poll:'true',
    chatroom_written : [false,false,false,false,false,false],

    current_smiley_selected: 0,
    d_id:freidefines.GEN.reidfrom,
    video_message_sent:false,
    video_rids: [],
    jscrollers: [],
    userdata: [],
    custom_gst_name: freidefines.GEN.fromname,
    g_prefix: freidefines.TRANS.g_prefix.replace("-","").toLowerCase(),
    jscroll: {} //all scroller objects for each chatwindow is stored here
};