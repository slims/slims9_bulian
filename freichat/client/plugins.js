//------------------------------------------------------------------------------
/* Chatroom font formatter plugin */

FreiChat.util = {
    storage: {
        get: function(index) {

            if (typeof Storage !== "undefined") {
                localStorage.getItem(index);
            } else {
                Get_Cookie(index);
            }
        },
        set: function(index, value) {

            if (typeof Storage !== "undefined") {
                localStorage.setItem(index, value);
            } else {
                Set_Cookie(index, value);
            }

        }

    }
};



FreiChat.plugins = {
    formatter: {
        id: "frei_chatroom_formatter_plugin",
        color: null,
        get_html: function() {

            var ident = this.id;
            var wrapper = '<div onclick="FreiChat.plugins.formatter.show_html()" id="' + ident + '" class="' + ident + '" > ';

            var body = '';

            var sel = '<div class="cp-default" id="frei_chatroom_cp"><div id="frei_chatroom_cp_content"></div></div>';
            var end = '</div>';

            return wrapper + body + end + sel;

        },
        load: function() {

            this.color = this.get_color();
            this.cp = $jn("#" + this.id);

            $jn("#frei_chatroom_cp_content").spectrum({
                color: this.color,
                showPaletteOnly: true,
                showPalette: true,
                palette: [
                    ['rgb(255, 255, 255)', 'rgb(0, 0, 0)', 'rgb(0, 0, 85)',
                        'rgb(0, 128, 0)', 'rgb(255, 0, 0)'],
                    ['rgb(128, 0, 0)', 'rgb(128, 0, 128)', 'rgb(255, 85, 0)',
                        'rgb(255, 255, 0)', 'rgb(0, 255, 0)'],
                    ['rgb(0, 128, 128)', 'rgb(0, 255, 255)', 'rgb(0, 0, 255)',
                        'rgb(255, 0, 255)', 'grey'],
                ], flat: true,
                change: function(tcolor) {
                    FreiChat.plugins.formatter.change_clr(tcolor);
                }

            });

            this.cp.css("background", this.color);
            $jn('#chatroommessagearea').css("color", this.color);
        },
        change_clr: function(color) {

            var rgba = color.toHexString();
            this.cp.css("background", rgba);
            this.set_color(rgba);

        },
        show_html: function() {

            $jn("#frei_chatroom_cp").show();
        },
        get_color: function() {

            return Get_Cookie('selected_chatroom_color');
        },
        set_color: function(color) {

            Set_Cookie('selected_chatroom_color', color);
            this.color = color;
            $jn('#chatroommessagearea').css("color", color);
        },
        formatBB: function(message) {

            if (this.color !== "#808080" || this.color !== "grey") {

                message = "[color=" + this.color + "]" + message + "[/color]";
            }

            return message;
        },
        format: function(message) {

            if (this.color !== "#808080" || this.color !== "grey") {

                message = "<span style='color:" + this.color + "'>" + message + "</span>";
            }

            return message;

        }
    },
    is_allowed: function(index) {

        var guests = (freidefines.GEN.is_guest == 1 && freidefines.ACL[index].guest == "allow");
        var users = (freidefines.GEN.is_guest == 0 && freidefines.ACL[index].user == "allow");

        return (guests || users);
    }
};

/*  The SMILEY plugin !*/
FreiChat.smiley = function(id)
{

    if (id == FreiChat.in_room) {
        id = "chatroom";
    }

    FreiChat.current_smiley_selected = id;

    var smileys = $jn('#frei_smileys_' + id);
    smileys.slideToggle();
};
//------------------------------------------------------------------------------
FreiChat.smileylist = function(id)
{
    var smileys = freidefines.smileys;

    var i = 0;

    var sm_array = [];

    for (i = 0; i < smileys.length; i++) {
        sm_array[i] = smileys[i].symbol;
    }

    var str;

    /*
     if(freidefines.thememaker == true) {
     str= '<span class="smileylist">'+FreiChat.mksmileyurl([':)',':(',':B',':\')',':laugh:',':cheer:',';)',':P',':angry:',':unsure:',':ohmy:',':huh:',':dry:',':lol:',':silly:',':woohoo:'], id)+'</span>';
     }else{
     str= '<span class="smileylist">'+FreiChat.mksmileyurl([':)',':(',':B',':\')',':laugh:',':cheer:',';)',':P',':angry:',':unsure:',':ohmy:',':huh:',':o',':0',':dry:',':lol:',':D',':silly:',':woohoo:'], id)+'</span>';
     }*/


    str = '<span class="smileylist">' + FreiChat.mksmileyurl(sm_array, id) + '</span>';


    return str;

};
//------------------------------------------------------------------------------
FreiChat.mksmileyurl = function(name, id)
{
    var namelen = name.length;
    var i = 0;
    var str = '<tr>';
    var j = 0;

    for (i = 0; i <= namelen; i++)
    {
        if (name[i] == null || name[i] == undefined)
        {
            break;
        }

        if (j >= 5)
        {
            str += '</tr><tr>';
            j = 0;
        }

        var action;

        if (freidefines.thememaker == true) {
            action = ''
        } else {
            action = 'onmousedown=FreiChat.appendsmiley("' + name[i] + '","' + id + '")';
        }

        str += '<td><div class="frei_smiley_image" ' + action + ' >' + FreiChat.SmileyGenerate(name[i], id) + '</div></td>';
        j++
    }
    //sconsole.log('<table><td>'+str+'</td></table>');
    return '<table class="frei_smileys_table">' + str + '</table>';
};
//------------------------------------------------------------------------------
FreiChat.appendsmiley = function(name, id)
{

    if (id == "chatroom") {
        id = "chatroommessagearea";
    } else if (id == "mobile") {
        id = "chat_message";
    } else {
        id = 'chatboxtextarea' + id;
    }
    var area = $jn('#' + id);

    $jn('#frei_smileys_' + id).css('display', 'none')
            .removeClass('inline')
            .addClass('none');

    area.val(area.val() + name + " ");

    setTimeout(function() {
        FreiChat.move_cursor_to_end(document.getElementById(id));
    }, 100);
};
//------------------------------------------------------------------------------

FreiChat.move_cursor_to_end = function(el) {
    if (typeof el.selectionStart == "number") {
        el.selectionStart = el.selectionEnd = el.value.length;
    } else if (typeof el.createTextRange != "undefined") {
        el.focus();
        var range = el.createTextRange();
        range.collapse(false);
        range.select();
    }
};
//------------------------------------------------------------------------------


FreiChat.SmileyGenerate = function(messages, id)
{
    var replaced_mesg = messages;


    var smileys = freidefines.smileys;
    var i = 0;
    for (i = 0; i < smileys.length; i++) {

        replaced_mesg = replaced_mesg.frei_smiley_replace(smileys[i].symbol, '<img id="smile__' + id + '" src="' + FreiChat.make_url(smileys[i].image_name,"smileys") + '" alt="smile" />');

    }
    return replaced_mesg;
};
//------------------------------------------------------------------------------

String.prototype.frei_smiley_replace = function(name, value) {
    name = name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
    var re = new RegExp(name, "g");
    return this.replace(re, value);
}

/*  The SMILEY plugin !*/
//------------------------------------------------------------------------------
/*  The MAIL plugin !*/

FreiChat.sendmail = function(user, id)
{
    FreiChat.toid = id;
    FreiChat.touser = user;

    var left = (screen.width - 450) / 2;
    var top = (screen.height - 250) / 2;

    FreiChat.is_chatroom = (FreiChat.in_room === id);

    window.open(freidefines.GEN.url + "client/plugins/mail/html.php", 'mailWindow', 'width=450,height=250,top=' + top + ',left=' + left);
};
/*  The MAIL plugin !*/
//------------------------------------------------------------------------------
/*  The TRANSLATE plugin !*/
FreiChat.changelang = function(lang, id)
{
    var CookieStatus = FreiChat.getCookie(id);

    if (lang == 'disable')
    {
        FreiChat.setCookie("frei_stat_" + id, "disable&opened&" + CookieStatus.chatwindow_2 + "&" + CookieStatus.message + "&" + CookieStatus.pos_top + "&" + CookieStatus.pos_left);
        $jn("#translateimage" + id).attr('src', FreiChat.make_url(freidefines.notransimg));
        $jn("#frei_trans" + id).slideToggle('slow');
    }
    else
    {
        $jn("#translateimage" + id).attr('src', FreiChat.make_url(freidefines.translateimg));
        FreiChat.setCookie("frei_stat_" + id, lang + "&opened&" + CookieStatus.chatwindow_2 + "&" + CookieStatus.message + "&" + CookieStatus.pos_top + "&" + CookieStatus.pos_left);
        $jn("#frei_trans" + id).slideToggle('slow');
    }
};
//------------------------------------------------------------------------------
FreiChat.translate = function(id)
{
    $jn("#frei_trans" + id).slideToggle();
};
//------------------------------------------------------------------------------
FreiChat.langlist = function(id)
{
    var str = '<span class="langlist">' + FreiChat.makelangurl(['en', 'de', 'zh', 'cy', 'tr', 'uk', 'ru', 'it', 'ja', 'el', 'iw', 'fr', 'gl', 'ar'], id) + '<br/><a href="javascript:void(0)" onmousedown=FreiChat.changelang("disable",\'' + id + '\')>' + freidefines.plugin_trans_disable + '</a>&nbsp;</span>';
    return str;
};
//------------------------------------------------------------------------------
FreiChat.makelangurl = function(name, id)
{
    var namelen = name.length;
    var i = 0;
    var str = '';
    for (i = 0; i <= namelen; i++)
    {
        if (name[i] == null || name[i] == undefined)
        {
            break;
        }
        str += '<a href="javascript:void(0)" onmousedown=FreiChat.changelang("' + name[i] + '",\'' + id + '\')>' + name[i] + '</a>&nbsp;';
    }
    return str;
};
//------------------------------------------------------------------------------
FreiChat.appendtranslate = function(language, id, arr)
{
    var div = null;
    if (arr[0] == 'callbyget')
    {
        div = $jn('#msg_' + arr[1]);
        div.translate(language, {
            not: '.notranslate'
        });
    }
    else
    {
        div = $jn("#frei_" + id + " .chatboxcontent");
        if (arr == null || arr == '')
        {
            div.translate(language, {
                not: '.notranslate'
            });
        }
        else
        {
            div.translate(language, {
                not: '.notranslate'
            });

        }

    }

};
//------------------------------------------------------------------------------
FreiChat.show_original_text = function(me, id)
{
    var show_by_delaying = function() {

        var pos = $jn(me).position();

        if ($jn("#frei_orig_" + id).hasClass('iamtobehovered'))
        {
            $jn("#frei_orig_" + id).css({
                "left": (pos.left - 30) + "px",
                "top": (pos.top - 50) + "px",
                "display": "block"
            });
        }
    };

    FreiChat.timer = setTimeout(show_by_delaying, 500);
};
//------------------------------------------------------------------------------
FreiChat.show_original_text_onhover = function(me)
{
    if ($jn(me).hasClass('iamtobehovered'))
    {
        $jn(me).addClass('iambeinghovered');
    }


};
//------------------------------------------------------------------------------
FreiChat.hide_original_text = function(id)
{
    var a = function() {
        if (!$jn("#frei_orig_" + id).hasClass('iambeinghovered'))

        {
            $jn("#frei_orig_" + id).css("display", "none");
        }
    };
    setTimeout(a, 500);
    clearTimeout(FreiChat.timer);
};
//------------------------------------------------------------------------------
FreiChat.hide_original_text_onout = function(id)
{

    var hide_by_delaying = function() {

        $jn("#frei_orig_" + id).removeClass('iambeinghovered');
        $jn("#frei_orig_" + id).css("display", "none");
    };

    setTimeout(hide_by_delaying, 500);

};
/*  The TRANSLATE plugin !*/
//------------------------------------------------------------------------------
/*  The UPLOAD plugin !*/
FreiChat.upload = function(user, id)
{
    FreiChat.toid = id;
    FreiChat.touser = user;
    var left = (screen.width - 400) / 2;
    var top = (screen.height - 200) / 2;

    FreiChat.secure_upload = true;
    FreiChat.is_chatroom = (FreiChat.in_room === id);

    window.open(freidefines.GEN.url + "client/plugins/upload/html.php", 'uploadWindow', 'width=400,height=200,top=' + top + ',left=' + left);
};
/*  The UPLOAD plugin !*/
//------------------------------------------------------------------------------
/*  The VIDEO plugin !*/

//-------------------------------------------------------------------------------
/* Time */

FreiChat.getlocal_time = function(GMT_time) {



    if (GMT_time == 0) {
        GMT_time = FreiChat.getGMT_time();
    }

    var d = FreiChat.Date;
    var offset = d.getTimezoneOffset() * 60000;
    var timestamp = GMT_time - offset;

    var dTime = new Date(timestamp);
    var hours = dTime.getHours();
    var minute = dTime.getMinutes();

    if (minute < 10) {
        minute = "0" + minute;
    }
    /*
     var period = "AM";
     if (hours > 12) {
     period = "PM"
     }
     else {
     period = "AM";
     }*/
    //hours = ((hours > 12) ? hours - 12 : hours)
    return hours + ":" + minute + " ";// + period
};
//-----------------------------------------------------------------------------------------------
FreiChat.getGMT_time = function() {

    var d = new Date();
    var localtime = d.getTime();
    var offset = d.getTimezoneOffset() * 60000;
    return localtime + offset;
};
//-----------------------------------------------------------------------------------------------
FreiChat.show_time = function(id) {

    if (freidefines.PLUGINS.chat_time_shown_always === 'no')
        $jn("#freichat_time_" + id).css("visibility", "visible");
};
//-----------------------------------------------------------------------------------------------
FreiChat.hide_time = function(id) {

    if (freidefines.PLUGINS.chat_time_shown_always === 'no')
        $jn("#freichat_time_" + id).css("visibility", "hidden");
};
//-----------------------------------------------------------------------------------------------
/* Time */
/* profile link */

FreiChat.show_profilelink = function(id) {
    $jn("#freichat_profile_link_" + id).css("visibility", "visible");
    $jn("#freichat_user_" + id).addClass('freichat_userlist_hover');
};
FreiChat.hide_profilelink = function(id) {
    $jn("#freichat_user_" + id).removeClass('freichat_userlist_hover');
    $jn("#freichat_profile_link_" + id).css("visibility", "hidden");
};


FreiChat.has_scrollbar = function(id) {
    var _elm = $jn("#" + id);
    var _hasScrollBar = false;
    if ((_elm.clientHeight < _elm.scrollHeight) || (_elm.clientWidth < _elm.scrollWidth)) {
        _hasScrollBar = true;
    }
    return _hasScrollBar;
};

//------------------------------------------------------------------------------
FreiChat.create_scrollbar = function(id, push) {
    var pane = $jn("#" + id);
    pane.nanoScroller({
        preventPageScrolling: true,
        scroll: 'bottom',
        alwaysVisible: true
    });

    if (typeof push === "undefined")
        push = true;

    if (push)
        FreiChat.jscrollers.push(id);

};
//------------------------------------------------------------------------------
FreiChat.show_prompt = function(mesg) {

    var mesg = prompt(mesg);

    return mesg;
};
//-------------scrolls down the scroller ---------------------------

FreiChat.update_custom_gst_name = function() {

    FreiChat.freichatopt("nooptions");

    var name = $jn('#custom_guest_name_id').val();

    if (FreiChat.name_exists(name)) {

        alert(freidefines.TRANS.custom_guest_name_exists);
        return;
    }

    var l_name = name.toLowerCase();
    if (l_name.indexOf(FreiChat.g_prefix) >= 0) {
        FreiChat.custom_gst_name = name;
    } else {
        FreiChat.custom_gst_name = FreiChat.mod_guest_name(name,false);
    }

};

FreiChat.mod_guest_name = function(name,dup) {
  
    if(dup) {
        
        return name + Math.floor(Math.random() * 90 + 10);
    }else{
        return name + " (" + FreiChat.g_prefix + ")";         
    }
};

FreiChat.name_exists = function(name) {
    
    var len = FreiChat.userdata.length;

    //here len>0 makes sure that len-- does not make len as -1 
    while (len>0 && len--) {

        if (name === FreiChat.userdata[len].show_name 
                || FreiChat.mod_guest_name(name,false) === FreiChat.userdata[len].show_name)
            break;
    }

    return !!len; 
};
//-------------scrolls down the scroller ---------------------------

FreiChat.scroll_down = function(ele_id, id) {

    //is called when content is added dynamically

    //reinistialize for every new message (dynamic content)
    if (id) {
        //FreiChat.jscroll[id].reinitialise();
        //FreiChat.jscroll[id].scrollToBottom();
    } else {
        //chatroom -> as false is passed in this case
    }
    //->bring scroller to bottom

    //get jquery div
    var div = $jn("#" + ele_id); //assuming only id of the element is passed
    //get height of div
    //var ht = div[0].scrollHeight; //DOM property 

    //set its scrolltop equal to its new scroll height
    if (FreiChat.jscrollers.indexOf(ele_id) != -1) {
        div.nanoScroller().nanoScroller({scroll: 'bottom'});
        //console.log(ele_id);
    }
};

/* profile link */
