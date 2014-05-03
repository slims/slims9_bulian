/*
 * =================
 * Rule of the thumb
 * =================
 * 
 * 
 * If any css property in the css object is an array 
 * then that property holds multiple values and hence should be looped
 * 
 * 
 * 
 * for eg.
 * 
 * CSS Object {
 * 
 *  #freichat: 
 *   {   
 *    //now here background is an array hence it contains multiple values
 *     background: Array : {
 *       -webkit-linear-gradient: ....
 *       linear-gradient: ....
 *     }
 *     
 *     //this is normal
 *     border: 1px solid
 * 
 *     .....
 *     .....
 *   }
 *   
 *  .... 
 *  ....
 *  .... 
 * }
 * 
 * 
 * =========
 * PUNCHLINE
 * =========
 * 
 * Choose a name that describes WHAT the object does, instead of how it does it.
 * 
 * 
 * ========
 * LICENSE
 * ========
 * 
 * Codologic 
 */


var FreiChat = {
    anim_inprogress: false,
    mesg: '',
    anim_notify_progress: false,
    notify_disappear_time: 5000,
    allow_upload: false,
    css_array: [],
    disable_doc_click: false,
    unsaved_changes: false,
    action_style_rules: false,
    state_class: [],
    notification_closed: true,
    mod_css_array: {}
};
//alert(freidefines.GEN.url);
freidefines.GEN.url = freidefines.GEN.url.replace("administrator/admin.php", "");

FreiChat.make_url = function(name) {

    var url = freidefines.GEN.url;
    var path = url + "client/themes/" + freidefines.GEN.curr_theme + "/" + name;
    return  path;
}

//-------------------------------------------------------------------------------------
FreiChat.check_indexes = function(pre, identity, tag_name) {

    var t_index = '';
    var index = '';
    identity = pre + $.trim(identity);

    if (identity != '') {
        t_index = identity + " " + tag_name.toLowerCase();
        if (typeof FreiChat.css_array[t_index] != "undefined") {
            index = t_index;
        } else {
            if (typeof FreiChat.css_array[identity] != "undefined") {
                index = identity;
            }
        }
    }
    return index;
}

//-------------------------------------------------------------------------------------
FreiChat.check_parents = function(el) {

    var array = [];
    var jq_el = $(el); //for cross-browser compatibility
    var tag_name = jq_el.prop("tagName");

    jq_el = jq_el.parent(); //start with its parent element

    var cls, id;

    var index = "";
    do {

        cls = jq_el.attr("class");
        id = jq_el.attr("id");


        //check for id and class with subs with class as first preference with subs
        if (typeof cls != "undefined" && cls != "")
            index = FreiChat.check_indexes(".", cls, tag_name);

        if (index == "" && typeof id != "undefined" && id != "")
            index = FreiChat.check_indexes("#", id, tag_name);

        jq_el = jq_el.parent(); //loop through parent

        if (index != "")
            break;

    } while (jq_el.attr("id") != "freichathtml" && jq_el.prop("tagName") != "BODY");


    if (index != "") {
        array = FreiChat.css_array[index];
        FreiChat.selected_class = index;
    }

    return array;
};
//-------------------------------------------------------------------------------------
FreiChat.get_config = function(el) {

    //parameters: variable(definitions.js) , id(to select img) , type , [ php variable(argument.php) , js variable(defintions.js) ]

    var src = el.src;
    var cls = el.className;
    var id = el.id;

    var id_c = id;
    if (id_c == '') {
        id_c = cls;
    }

    if (!$('#' + id_c).is("img")) {
        //FreiChat.notify('the clicked element is not an image');
        return false;
    }



    var img = src.substring(src.lastIndexOf('/') + 1);
    var arr = img.split(".");

    var img_name = arr[0]; //1st 
    var img_ext = arr[1];


    return {
        name: img_name,
        id: id,
        cls: cls,
        type: 'img',
        ext: img_ext
    };
};
//-------------------------------------------------------------------------------------
/**
 * 
 * Highlights the content inside the element
 * @param {HTML element} element
 * @returns {undefined}
 */
FreiChat.select = function(element) {
    var doc = document;
    if (doc.body.createTextRange) {
        var range = document.body.createTextRange();
        range.moveToElementText(element);
        range.select();
    } else if (window.getSelection) {
        var selection = window.getSelection();
        var range = document.createRange();
        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);
    }

};

FreiChat.get_caret_position = function(el) {

    var caretPos = 0, containerEl = null, sel, range;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.rangeCount) {
            range = sel.getRangeAt(0);
            if (range.commonAncestorContainer.parentNode == el) {
                caretPos = range.endOffset;
            }
        }
    } else if (document.selection && document.selection.createRange) {
        range = document.selection.createRange();
        if (range.parentElement() == el) {
            var tempEl = document.createElement("span");
            el.insertBefore(tempEl, el.firstChild);
            var tempRange = range.duplicate();
            tempRange.moveToElementText(tempEl);
            tempRange.setEndPoint("EndToEnd", range);
            caretPos = tempRange.text.length;
        }
    }
    return caretPos;

};

function highlight(el, st, en) {
    if (window.getSelection) {
        var range = document.createRange();

        var e = el.get(0);
        e = e.firstChild;
        range.setStart(e, st);
        range.setEnd(e, en);
        range.collapse(true);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);

    }
}


FreiChat.key_change_value = function(property, value) {
    if (value.indexOf("em") > -1 || value.indexOf("%") > -1 || value.indexOf("px") > -1) {
        //bind keydown event
        $jn('#input_style_' + property).on("keydown", function(e) {

            if (e.which === 38 || e.which === 40) {
                e.preventDefault();
                var me = $jn(this);
                me.focus();

                var vs = me.get_value().split(" ");
                var el = document.getElementById(me.attr("id"));

                var start = FreiChat.get_caret_position(el);

                var len = 0;
                var el_no = 0;
                for (var i = 0; i < vs.length; i++) {
                    len = len + vs[i].length + 1;
                    //console.log(len);
                    if (start < len) {
                        len--;
                        el_no = i;
                        break;
                    }
                }


                //highlight the text that will be inc/dec
                ///console.log(len);
                //console.log(vs[el_no]);
                var end_highlight = len,
                        start_highlight = len - vs[el_no].length;

                // highlight(me,start_highlight,end_highlight);

                var unit;
                var number = me.get_value().substring(start_highlight, end_highlight);

                if (number.indexOf("em") > -1) {
                    unit = "em";
                } else if (number.indexOf("%") > -1) {
                    unit = "%";
                } else if (number.indexOf("px") > -1) {
                    unit = "px";
                } else {
                    //unit = ""; //unitless
                    return;
                }


                number = parseInt(number);
                var step = 1;

                if (e.shiftKey === false)
                    step = 1;
                else
                    step = 10;

                if (e.which === 38)
                    number += step;
                else if (e.which === 40)
                    number -= step;

                //replace text -> inc/dec value
                me.get_value(me.get_value().substring(0, start_highlight) + number + unit + me.get_value().substring(end_highlight, me.get_value().length));


                var selection_start = start_highlight;
                //console.log(start_highlight);
                var selection_end = el.innerHTML.indexOf(unit, start_highlight) + unit.length;
                highlight(me, selection_start, selection_end);
            }

            //check if selection is in input

        });
    }

};
//-------------------------------------------------------------------------------------

FreiChat.edit_css = function(el, get_parent) {

    FreiChat.c_upload_config = FreiChat.get_config(el);

    if (FreiChat.c_upload_config) {
        $('#replace_image').show();
    } else {
        $('#replace_image').hide();
    }


    if (typeof get_parent === "undefined")
        el.classList.remove("mouseOn");

    var cls = el.className;
    var id = el.id;


    var tag = $(el).prop("tagName");

    if (id != '') {
        tag += "#" + id;
    }

    if (cls != '') {
        tag += "." + cls;
    }

    $("#style_rules_description").html(tag);


    var cls_sel = "." + cls;

    if (cls == '') {
        cls = id;
        cls_sel = "#" + id;
    }

    id = cls_sel;

    if (cls_sel != '' || cls_sel != null)
        FreiChat.selected_class = cls_sel;
    var arr = FreiChat.css_array;
    var k_arr;

    if (typeof arr[id] !== "undefined") {
        k_arr = arr[id];
    }
    else {
        k_arr = FreiChat.check_parents(el);
    }

    var property, name, value, len, i = 0, index;
    var str = '<table id ="table_add_style" >';
    //$('#style_rules_content').html('');


    for (property in k_arr) {

        if ($.isArray(k_arr[property])) {
            //contained duplicate property values
            //so change the

            len = k_arr[property].length;
            name = property;

            for (i = 0; i < len; i++) {
                value = k_arr[name][i];

                if (i === 0) {
                    index = '';
                } else {
                    index = '_D_' + i; //to identify whether its a duplicate
                }

                property = name + index;

                str += FreiChat.generate_row(name, property, value, '');

            }

        } else {
            //is unique
            name = property;
            value = k_arr[name];
            str += FreiChat.generate_row(name, property, value, '');
        }

    }
    $('#style_rules_content').html(str + "</table>");
    FreiChat.table_changed($('#style_rules_content'));

    var properties = FreiChat.deep_copy(k_arr);

    for (property in properties) {
        //if(properties[property].indexOf('<\?php') != -1) continue;          

        var name = property;
        var value = properties[name];

        if (!$.isArray(properties[name])) {
            properties[name] = [properties[name]];
        }

        var len = properties[name].length;

        for (i = 0; i < len; i++) {
            if (i === 0) {
                index = '';
            } else {
                index = '_D_' + i; //to identify whether its a duplicate
            }

            property = name + index;
            (function(ppty) {

                value = properties[name][i];
                $('#input_style_' + property).bind('textchange', function(event, previousText) {
                    FreiChat.apply_css('#input_style_' + ppty, ppty, id, previousText)
                }).click(function() {
                    var el = document.getElementById($(this).attr("id"));
                    FreiChat.select(el);
                });

                FreiChat.key_change_value(property, value);

                $('#tr_style_' + property).mouseover(function() {
                    $('#close_style_' + ppty).css('display', 'inline-block');
                }).mouseout(function() {
                    $('#close_style_' + ppty).hide();
                });

                $('#close_style_' + property).click(function() {
                    FreiChat.delete_style(ppty, '', $(this));
                });
            })(property);

        }

    }


    if ($('#style_rules').is(":visible")) {
        if (prev_selected_element) {
            prev_selected_element.classList.remove("selected_element");
            prev_selected_element = false;
        }
        $('#element_states').hide();
        $('#actions_style_rules').show();
        $('#style_rules_body').show();

    }
    //}



    ////dialog('open');
    if (selected_element) {
        selected_element.classList.add("selected_element");
        prev_selected_element = selected_element;
    }

    $('#style_rules').show();


    if (!FreiChat.action_style_rules) {
        $('#actions_style_rules').show(); //one time
        FreiChat.action_style_rules = "visible";
    }

    if (FreiChat.is_obj_empty(k_arr) == true) {
        $('#table_add_style').html("No styles to display!");
    }

};
//-------------------------------------------------------------------------------------

FreiChat.return_to_elements = function() {
    FreiChat.action_style_rules = false;
    $('#element_states').hide();
    $('#actions_style_rules').hide();
    $('#style_rules_body').hide();
    $('#style_rules_description').html('<div style="text-align:center;">please select any element from left</div>');

}
//-------------------------------------------------------------------------------------
FreiChat.return_to_normal = function() {
    $('#element_states').hide();
    $('#actions_style_rules').show();
    $('#style_rules_body').show();

}
//-------------------------------------------------------------------------------------
FreiChat.is_obj_empty = function(object) {
    var i;
    for (i in object) {
        if (object.hasOwnProperty(i))
            return false;
    }
    return true;
};
//-------------------------------------------------------------------------------------
FreiChat.is_valid_color = function(color) {

    color = tinycolor(color);

    return color.ok;
};
//-------------------------------------------------------------------------------------
/**
 * 
 * Copies a object without refrence
 * Note: copying != cloning
 * 
 * @param {Object} obj
 * @returns {@exp;jQuery@call;extend}
 */
FreiChat.deep_copy = function(obj) {
    return jQuery.extend({}, obj);
};
/**
 * Gets positions of colors in a string
 * 
 * @param {String} str
 * @returns {Array}
 */
FreiChat.get_color_positions = function(str) {

    var color_ids = ["#", "rgb", "hsl", "hsv"],
            color_names = freidefines.color_names; //color names name:hex [hex without hash]

    var t_color_ids = color_ids.concat(color_names);


    /*
     * 
     * H -> HEX colors #
     * R -> rgb,rgba,hsl,hsla colors
     * N -> color names
     * 
     */

    //simply cant trust the user input
    str = str.toLowerCase();

    var len = t_color_ids.length,
            indices = [],
            index = 0,
            go_next_id,
            type;

    for (var i = 0; i < len; i++) {

        go_next_id = false;
        while (!go_next_id) {
            index = str.indexOf(t_color_ids[i], index);

            if (index > -1) {

                if (i === 0) {
                    type = 'H';
                }
                else if (i <= 3) {
                    type = 'R';
                } else {

                    if (str.match("\\b" + t_color_ids[i] + "\\b") === null) {
                        index += t_color_ids[i].length;
                        continue;
                    }
                    type = t_color_ids[i].length;
                }

                indices.push([index, type]);
                index += t_color_ids[i].length;
            } else {
                go_next_id = true;
                index = 0; //start from beginning
            }
        }
    }

    //sort in ascending order
    indices.sort(function(a, b) {
        return a[0] - b[0];
    });

    return indices;
};

/*
 * extracts color acc to position in given string
 * 
 * @param {String} str
 * @param {Int} index
 * @returns {undefined}
 */
FreiChat.get_color = function(str, index, type) {

    var color_len,
            st,
            end,
            color;

    if (type === 'H') {
        st = index;
        //check hex6
        color_len = 7;
        color = str.substr(st, color_len);

        var valid = FreiChat.is_valid_color(color);

        if (!valid) {

            //check hex3
            color_len = 4;
            color = str.substr(st, color_len);
        }

    } else if (type === 'R') {
        //ending is definitely `)`

        st = index;
        end = str.indexOf(")", index);
        color_len = (end - st) + 1; //try it on your fingers 5-1 , what do you get ?? ;)
        color = str.substr(st, color_len);

    } else {
        //one of the color names
        //so length is direct

        st = index;
        color_len = type;
        color = str.substr(st, color_len);

    }

    return color;

};
//-------------------------------------------------------------------------------------
/**
 * 
 * 
 * 
 * @param {string} color string
 * @returns {string} modified color string 
 */
FreiChat.color_set = function(str) {

//TODO: add a learner function to learn properties that can have colors
// so that next time this function does not have to be run with properties
// that wont have color as their value

    var index = 0,
            color, //actual color in the string
            valid;

    //no problem of duplicates

    //get color positions    
    var indices = FreiChat.get_color_positions(str),
            len = indices.length,
            index, type;


    var inc = 0;

    for (var i = 0; i < len; i++) {
        index = inc + indices[i][0];
        type = indices[i][1];
        valid = false;


        color = FreiChat.get_color(str, index, type);
        valid = FreiChat.is_valid_color(color);


        if (valid) {
            str = FreiChat.add_color_sel(str, index, color);
            inc += str[1] - color.length; //get the correct no of characters add in the string
            str = str[0]; //get actual string       
        }
    }


    return str;
};
//-------------------------------------------------------------------------------------
/*
 * Prepends a color selector to specified color present in val string
 * 
 * @param {String} val
 * @param {Array} pos
 * @returns {Array}
 */
FreiChat.add_color_sel = function(val, pos, color) {

    var str = "<span class='color_selector_p'><div class='color_selector special'></div><span>" + color + "</span></span>";


    val = val.substr(0, pos) + str + val.substr(color.length + pos);

    return [val, str.length];
};
//-------------------------------------------------------------------------------------
/**
 * 
 * checks if given value is valid for given property
 * 
 * @param {String} property
 * @param {String} value
 * @returns {Boolean}
 */
FreiChat.is_valid_css = function(property, value) {

    var valid = false;

    if (typeof value === "undefined") {
        //check for valid property


    } else {

        var div = $('#css_tester_div');
        var old_val = div.css(property);

        div.css(property, value);
        var new_val = div.css(property);

        //valid if old value and new value are different
        valid = (old_val !== new_val);

        //revert to old value for reuse
        div.css(property, old_val);
    }

    return valid;
};
//-------------------------------------------------------------------------------------
FreiChat.reset_gradients = function() {

    var gradient;

    for (var i = 0; i < FreiChat.gradient_selectors.length; i++) {

        gradient = FreiChat.gradient_selectors[i].next().text();
        gradient = FreiChat.color_set(gradient);
        FreiChat.gradient_selectors[i].next().html(gradient);
    }

    $('#gradient_selector_content_div').html('');
    FreiChat.table_changed(FreiChat.gradient_selectors[0].parents("table"));
};
//-------------------------------------------------------------------------------------
FreiChat.click_gradx = function() {
    var gradient = FreiChat.correct_gradient,
            g_type = "linear",
            direction = "left",
            sliders = [];

    $('#gradient_selector_content').dialog('open');
    $('.ui-dialog').css({
        left: "auto",
        right: "0px",
        top: "100px"
    });

    $('.ui-dialog').draggable("option", "containment", 'body');

    if (typeof gradient !== "undefined") {

        if (gradient.indexOf("radial") > -1) {
            g_type = "radial";
        }

        var directions = ["center", "top", "bottom", "left", "right"];
        var present_dirs = [], index = 0, next = false;

        for (var i = 0; i < directions.length; i++) {

            next = false;
            index = 0;
            while (!next) {
                index = gradient.indexOf(directions[i], index);

                if (index > -1) {

                    present_dirs.push([directions[i], index]);
                    index += directions[i].length; //we dont want a infinite loop
                } else {
                    next = true;
                }

            }
        }

        var len = present_dirs.length;
        if (len <= 2) {

            //sort acc to index
            present_dirs.sort(function(a, b) {
                return a[1] - b[1];
            });

            if (present_dirs.length === 0) {
                direction = "left";
            }
            else if (present_dirs.length === 1) {
                direction = present_dirs[0][0];
            } else {
                direction = present_dirs[0][0] + "," + present_dirs[1][0];

            }
        } else {
            //something is wrong
            console.log("too many gradient directions")
        }

//TODO: http://www.glazman.org/JSCSSP/freshmeat.html

        var indices = FreiChat.get_color_positions(gradient);
        var len = indices.length,
                index, type, color, end, length, start,
                position, positions = [], colors = [];

        for (var i = 0; i < len; i++) {

            index = indices[i][0];
            type = indices[i][1];

            color = FreiChat.get_color(gradient, index, type);

            start = index + color.length;
            end = gradient.indexOf(",", start);

            length = end - start;

            position = gradient.substr(start, length);
            position = position.replace(/^\D+/g, '');
            position = $.trim(position);
            position = position.replace("%", "");

            if (position === "") {

                if (i === 0) {
                    positions[i] = 0;
                }
                else if (i === len - 1) {
                    positions[i] = 100;
                } else {
                    positions[i] = position;
                }


            } else {

                positions[i] = position;
            }

            colors[i] = color;

        }

        for (var i = 0; i < len; i++) {

            sliders.push({
                color: colors[i],
                position: positions[i]
            });
        }

        console.log(indices);
        console.log(sliders);

    }
    //invoke random

    var gradient_selectors = FreiChat.gradient_selectors;
    gradX('#gradient_selector_content_div', {
        type: g_type,
        direction: direction,
        targets: FreiChat.gradient_selectors,
        sliders: sliders,
        change: function(stops, styles) {

            var vendors = ["", "-o-", "-ms-", "-moz-", "-webkit-"];
            var len = vendors.length;
            var g_len = gradient_selectors.length,
                    gradient, div;

            for (var j = 0; j < g_len; j++) {
                div = FreiChat.gradient_selectors[j].next();
                gradient = div.text();

                for (var i = 0; i < len; i++) {

                    if (gradient.indexOf(vendors[i]) > -1) {
                        div.html(styles[i]);
                        div.parent().trigger("textchange");
                    }
                }

            }
        }

    });

    return false;
};

//-------------------------------------------------------------------------------------
/*
 * called when table is filled with the css roperties of clicked element
 * @returns {undefined}
 */
FreiChat.table_changed = function(tbl) {

    var change_clr = function(tcolor, me) {
        var color = tcolor.toString();
        me.css("background", color);
        var parent = me.parent();
        parent.find('span').text(color);
        parent.trigger("textchange");

    };

    var apply_gradient = function(arr, gradient) {
        for (var i = 0; i < arr.length; i++) {
            arr[i].css("background", gradient);
        }
    };


    var gradient_selectors = [],
            gradient_unapplied_selectors = [],
            correct_gradient = "";
    //.find is slightly slightly faster than direct $('.color_selector')

    tbl.find('.gradient_selector').each(function() {

        var me = $jn(this);

        var gradient = me.next().text();

        if (FreiChat.is_valid_css("background-image", gradient)) {
            me.css("background", gradient);

            correct_gradient = gradient;
            if (correct_gradient !== "") {
                //new latest correct gradient
                //apply this to all

                apply_gradient(gradient_selectors, gradient);
            } else {
                //apply to unapplied selectors
                apply_gradient(gradient_unapplied_selectors, correct_gradient);

            }

            gradient_unapplied_selectors = [];
            correct_gradient = gradient;
        } else if (correct_gradient !== "") {
            //invalid but a valid gradient exists
            me.css("background", correct_gradient);
            apply_gradient(gradient_unapplied_selectors, correct_gradient);
            gradient_unapplied_selectors = [];

        } else {
            gradient_unapplied_selectors.push(me);
        }

        gradient_selectors.push(me);
    });

    if (correct_gradient !== "") {
        FreiChat.correct_gradient = correct_gradient;

    }

    FreiChat.gradient_selectors = gradient_selectors;


    $jn('.gradient_selector').on({
        click: function() {
            FreiChat.click_gradx();
            return false;
        }
    });

    var len = gradient_unapplied_selectors.length;

    if (len > 1) {
        //there is some problem

        for (var i = 0; i < len; i++) {
            //apply a default gradient
            gradient_unapplied_selectors[i].css("background", "#eee");
        }
    }

    tbl.find('.color_selector').each(function() {

        var me = $jn(this);
        var parent = me.parent();
        var color = parent.find('span').text();

        me
                .css("background", color)
                .spectrum({
                    color: color,
                    clickoutFiresChange: true,
                    showInput: true,
                    showButtons: false,
                    showAlpha: true,
                    move: function(tcolor) {
                        change_clr(tcolor, me);
                    },
                    change: function(tcolor) {
                        change_clr(tcolor, me);
                    }


                })
                .html("");
    });
};
//-------------------------------------------------------------------------------------
/**
 * 
 * Returns string with gradient selector prepended if value i.e str contains gradient
 *
 * 
 * @param {String} str
 * @returns {String}
 */
FreiChat.gradient_set = function(str) {

    if (str.indexOf("gradient") > -1) {
        //obviously gradient word will be present 

        var grad = "<div class='gradient_selector special'></div>";

        str = grad + "<span>" + str + "</span>";

    }

    return str;
};
//-------------------------------------------------------------------------------------
/**
 * Generates the HTML for displaying property value as a row in a table
 * 
 * @param {String} name
 * @param {String} property
 * @param {String} value
 * @param {String} state
 * @returns {String}
 */
FreiChat.generate_row = function(name, property, value, state) {

    return '<tr  id="tr_' + state + 'style_' + property + '"><td class="td_property"><span class="font_style_rules" >' + name + ':</span></td><td>' + FreiChat.get_property_value(property, value) + FreiChat.generate_close_btn(property, state) + '</td></tr>';
};
//-------------------------------------------------------------------------------------

/**
 * Generates the HTML for the close button 
 * 
 * @param {String} property
 * @returns {String}
 */
FreiChat.generate_close_btn = function(property, state) {
    return '<span id="' + state + 'close_style_' + property + '" class="close_style_rules"><a title="delete style">X</a></span>';
};
//-------------------------------------------------------------------------------------

/**
 * returns formatted div which is substituted by an input onclick
 * and resubstituted to div onblur 
 * 
 * 
 * @param {string} property is name of the css property 
 * @param {string} value is the name of the css value
 * @returns {string} formatted string
 */
FreiChat.get_property_value = function(property, value) {

    value = FreiChat.color_set(value);
    value = FreiChat.gradient_set(value);

    //TODO: make all other event delegated for performance

    //event delegation
    //jquery defines cross-browser focusin and focusout instead of focus and blur
    //because these events do not bubble up according to W3C

    FreiChat.has_clicked = false;

    //mousedown happens before focus
    //just a simple hack for tab key simulation
    $jn("#element_css_container").on({
        click: function(event) {
            FreiChat.has_clicked = true;

            if (!$(event.target).hasClass("special")) {
                $(this).find('.special').each(function() {
                    $(this).hide();
                    console.log($(this));
                });
            } else {
                if ($(event.target).hasClass("gradient_selector"))
                    FreiChat.click_gradx();
            }

        },
        mousedown: function(event) {
            FreiChat.has_clicked = true;

            if (!$(event.target).hasClass("special")) {
                $(this).find('.special').each(function() {
                    $(this).hide();
                    console.log($(this));
                });
            } else {
                if ($(event.target).hasClass("gradient_selector"))
                    FreiChat.click_gradx();
            }
        },
        focusin: function(event) {

            if (!FreiChat.has_clicked) {
                $(this).find('.special').each(function() {
                    $(this).hide();
                });
            }
            FreiChat.has_clicked = false;
        },
        focusout: function() {

            var me = $(this);

            me.find('.special').each(function() {
                $(this).show();
                console.log($(this));
            });

            var str = FreiChat.color_set(me.text());
            str = FreiChat.gradient_set(str);
            me.html(str);
            FreiChat.table_changed(me.parents("table"));
        }
    }
    , '#input_style_' + property);

    return '<div contenteditable class="td_value" id="input_style_' + property + '">' + value + '</div>';
    //return '<input class="input_style_rules" id="input_style_' + property + '" type="text" value="' + value + '" />';
};
//-------------------------------------------------------------------------------------

/**
 * 
 * @param {string} selected_class name of class in css_array  
 * @param {string} property name of property in css_array[selected_class] 
 * @param {String} action change(value will be passed in action itself) | delete | check
 * @returns {boolean|string}
 *  if action is check and property exists returns true else false
 *  else returns true property name after stripping off _D_index    
 *  
 */
FreiChat.modify_css_array = function(selected_class, property, action) {

    var name = property;

    var parts = property.split("_D_");
    property = parts[0];

    if ($.isArray(FreiChat.css_array[selected_class][property])) {
        //property will be an array of multipl values for same property

        //get index 
        var index = parts[1]; //assuming there only one _D_ in the property name
        name = parts[0];

        //css_array modification will be 3 dimensional
        if (!(selected_class in FreiChat.mod_css_array)) {
            FreiChat.mod_css_array[selected_class] = {};
        }

        if (!(name in FreiChat.mod_css_array[selected_class])) {
            FreiChat.mod_css_array[selected_class][name] = {};
        }

        if (action === "delete") {
            delete  FreiChat.css_array[selected_class][name][index];
            delete  FreiChat.mod_css_array[selected_class][name][index];
        } else if (action === 'check') {
            return (typeof FreiChat.css_array[selected_class][name][index] === "undefined");
        } else {
            FreiChat.css_array[selected_class][name][index] = action;
            FreiChat.mod_css_array[selected_class][name][index] = action;
        }
    } else {
        //single property single value

        if (!(selected_class in FreiChat.mod_css_array)) {
            FreiChat.mod_css_array[selected_class] = {};
        }

        if (action === "delete") {
            delete FreiChat.css_array[selected_class][property];
            delete FreiChat.mod_css_array[selected_class][property];
        } else if (action === 'check') {
            return (typeof FreiChat.css_array[selected_class][property] === "undefined");
        } else {
            FreiChat.css_array[selected_class][property] = action;
            FreiChat.mod_css_array[selected_class][property] = action;
        }
    }

    return name;
};
//-------------------------------------------------------------------------------------
FreiChat.delete_style = function(property, selected_class, ele) {

    var state = 'state';

    //dirty trick , but does the job :)
    if (ele.attr("id").indexOf("state_") > -1) {
        selected_class = FreiChat.selected_class;
        state = '';
        $(FreiChat.selected_class).css(property, 'inherit');
    }


    $('#tr_' + state + 'style_' + property).css('display', 'none');

    FreiChat.modify_css_array(selected_class, property, "delete");
    // FreiChat.css_array[selected_class][property];//alert(property);
};
//-------------------------------------------------------------------------------------
FreiChat.add_styles = function() {

    FreiChat.process_styles("add_styles_value", FreiChat.selected_class, "multiple_styles");
    $('#add_styles_textarea').hide();
};
//-------------------------------------------------------------------------------------

FreiChat.add_state_styles = function() {

    var selected_class = FreiChat.selected_state_class;
    FreiChat.process_styles("add_state_styles_value", selected_class, "multiple_state_styles");
    $('#add_state_styles_textarea').hide();
}
//-------------------------------------------------------------------------------------

FreiChat.process_styles = function(id, selected_class, action) {

    var text = $.trim($('#' + id).get_value());
    text = text.replace(/[\n\r]/g, '');

    var styles = text.split(";");
    var len = styles.length, property, value, parts, i;

    var _styles = [];
    var properties = [];

    for (i = 0; i < len; i++) {

        if (styles[i] === "")
            continue;

        parts = styles[i].split(":");
        property = $.trim(parts[0]);
        value = $.trim(parts[1]);

        if (properties.indexOf(property) === -1) {
            //property does not contain the property 
            _styles.push([property, value]);
        } else {
            //duplicate entries here
            if (typeof _styles[property] === "undefined") {
                _styles[i] = [];
            }
            _styles[i].push([property, value]);
        }

        properties.push(property);

    }

    len = _styles.length;

    var name, index;
    for (i = 0; i < len; i++) {

        if (_styles[i] instanceof Array) {

            for (var j = 0; j < _styles[i].length; j++) {
                name = _styles[i][j][0];

                if (j === 0) {
                    index = '';
                } else {
                    index = '_D_' + i; //to identify whether its a duplicate
                }

                property = name + index;

                value = _styles[i][j][1];
                FreiChat.add_new_style(selected_class, action, property, name, value);

            }
        } else {
            property = _styles[i][0];
            value = _styles[i][1];
            FreiChat.add_new_style(selected_class, action, property, name, value);
        }
    }

};
//-------------------------------------------------------------------------------------
/*
 * 
 * @param {type} property
 * @returns {if property exists returns property_D_[lastindex+1]}
 */
FreiChat.correct_property = function(selected_class, property) {

    if ($.isArray(FreiChat.css_array[selected_class][property])) {
        property += "_D_" + FreiChat.css_array[selected_class][property].length;
    }

    return property;
};
//-------------------------------------------------------------------------------------
FreiChat.add_new_style = function(selected_class, is_a_state, property, name, value) {

    if (typeof selected_class === "undefined")
        selected_class = FreiChat.selected_class;

    if (typeof is_a_state === "undefined")
        is_a_state = false;


    var state = '';
    var table_id = '_add_style';

    if (is_a_state && is_a_state != "multiple_styles") {
        state = 'state_';
        table_id = '_' + selected_class;
        table_id = FreiChat.fix_id(table_id);
    }

    if (typeof FreiChat.mod_css_array[selected_class] === "undefined") {
        FreiChat.mod_css_array[selected_class] = {};
    }

    if (is_a_state != "multiple_styles" && is_a_state != "multiple_state_styles") {
        name = $.trim($('#property_add_' + state + 'style').get_value());
        if (typeof FreiChat.css_array[selected_class][name] !== "undefined") {

            if (!$.isArray(FreiChat.css_array[selected_class][name])) {
                //property already exists so create an array to store duplicate property values
                FreiChat.css_array[selected_class][name] = [FreiChat.css_array[selected_class][name]];
            }
        }

        if (typeof FreiChat.mod_css_array[selected_class][name] !== "undefined") {

            if (!$.isArray(FreiChat.mod_css_array[selected_class][name])) {
                //property already exists so create an array to store duplicate property values
                FreiChat.mod_css_array[selected_class][name] = [FreiChat.mod_css_array[selected_class][name]];
            }
        }
        property = FreiChat.correct_property(selected_class, name);
        value = $.trim($('#value_add_' + state + 'style').get_value());
    }

    if (typeof FreiChat.css_array[selected_class] === "undefined") {
        FreiChat.css_array[selected_class] = {}
    }

    if (property === '' || value === '') {
        FreiChat.notify('you cannot leave the fields empty');
        return;
    }


    var str = FreiChat.generate_row(name, property, value, state);

    //var js_ele = document.getElementById("table_"+table_id);
    var tbl = $("#table" + table_id);

    if (tbl.html() === "No styles to display!") {
        //first style
        tbl.html(str);
    } else {
        //tbl.find("tr").last().after(str);
        $("#table" + table_id + " tr:last").after(str);
    }

    FreiChat.table_changed(tbl);

    if (!is_a_state) {
        $('#input_style_' + property).bind('textchange', function(event, previousText) {
            FreiChat.apply_css('#input_style_' + property, property, FreiChat.selected_class, previousText)
        });
    }

    $('#tr_' + state + 'style_' + property).mouseover(function() {
        $('#' + state + 'close_style_' + property).css('display', 'inline-block');
    }).mouseout(function() {
        $('#' + state + 'close_style_' + property).hide();
    });

    $('#' + state + 'close_style_' + property).click(function() {
        FreiChat.delete_style(property, selected_class, $(this));
    });



    FreiChat.unsaved_changes = true;
    FreiChat.modify_css_array(selected_class, property, value);

    if (!is_a_state) {
        $('#add_new_style_content').hide();
        $(FreiChat.selected_class).css(name, value);
    } else {
        $('#add_new_state_style_content').hide();
    }
};
//-------------------------------------------------------------------------------------

/*
 * Checks for properties with same name after itself
 *  
 * @param {string} class_name
 * @param {string} ppty
 * @returns {boolean} true if property with same name exists down the array
 * 
 */
FreiChat.chk_duplicate_property = function(class_name, ppty) {

    if ($.isArray(FreiChat.css_array[class_name][ppty])) {

        var i, len = FreiChat.css_array[class_name][ppty].length,
                parts = ppty.split("_D_");

        var index = parts[1];
        --len; //starts from 0

        if (typeof index === "undefined") {
            index = 0; //if it is the first property
        }

        if (index < len) {
            //there is one more property down there
            return true;
        }
    }

    return false; //only or last property  
};
//-------------------------------------------------------------------------------------
FreiChat.apply_css = function(div, ppty, css_div, oldval, use_passed_id) {
    var val = $(div).get_value(),
            class_name, name;
    console.log(val);
    if (oldval === $.trim(val)) {
        return; //value is unchanged
    }

    if (val === '') {
        val = 'none'; //specific buggy code here
    }

    val = FreiChat.correct_path(val);

    if (use_passed_id) {
        class_name = css_div;
    } else {
        class_name = FreiChat.selected_class;
    }

    name = FreiChat.modify_css_array(class_name, ppty, val);

    //see function for description
    if (FreiChat.chk_duplicate_property(class_name, ppty)) {
        //just notify the user that his changes may not apply to the left div


        if (FreiChat.notification_closed) {
            FreiChat.notification_closed = false;
            $.noty({
                text: " a property with the same name exists somewhere below , so changing this one may not affect the element on the left",
                onClose: function() {
                    FreiChat.notification_closed = true;
                }
            });
        }
    } else {
        $(css_div).css(name, val);
    }

    FreiChat.unsaved_changes = true;

};
//-------------------------------------------------------------------------------------
FreiChat.correct_path = function(value) {

    if (value.indexOf("url") > -1) {

        //contains a url
        var path = freidefines.GEN.url;
        path = path + "client/themes/";

        var theme = freidefines.GEN.curr_theme;
        path = path + theme + "/";
        value = value.replace(/'/g, "");
        value = value.replace(/"/g, "");

        var i = 0, len = value.length, start, stop = 0, url;



        for (i = 0; i < len; i++) {
            start = value.indexOf("(", stop);
            stop = value.indexOf(")", start);

            if (start === -1)
                break;

            url = value.substring(start + 1, stop);
            value = value.replace(url, path + url);
            i = stop;
        }

    }

    return value;
}
//-------------------------------------------------------------------------------------
FreiChat.get_states = function() {

    //i can directly use FreiChat.selected_class here :)
    var
            curr_class = FreiChat.selected_class,
            key,
            states = [], //array of object styles
            label,
            property,
            value,
            styles,
            str = '',
            str_styles = '',
            index,
            name,
            id,
            len, i
            ; //for adding more vars


    for (key in FreiChat.css_array) {

        if (key.indexOf(curr_class) > -1 && key != curr_class) {
            states[key] = FreiChat.css_array[key];
        }
    }


    for (key in states) {

        //key -> complete classes matching FreiChat.selected_class
        // i.e FreiChat.selected_class:hover, FreiChat.selected_class:after

        //getting states like :hover, :after from full class
        label = $.trim(key).replace(FreiChat.selected_class, "");

        str += '<div class="state_label"><div class="state_label_text">' + label + '</div><div onclick=\'FreiChat.add_new_state_styles("' + key + '",true)\' class="state_add_new_style_btn btn"> add css</div><div onclick=\'FreiChat.add_new_state_style("' + key + '",true)\' class="state_add_new_style_btn btn"> add new style</div></div>';

        id = FreiChat.fix_id(key);

        str_styles = '<table id="table_' + id + '" class="table_state_styles"><tbody>';


        if (FreiChat.is_obj_empty(states[key])) {
            $('#table_' + id).html("No styles to display!");
        }


        for (styles in states[key]) {

            name = styles;
            property = styles;

            if ($.isArray(states[key][styles])) {
                //contained duplicate property values
                //so change the

                len = states[key][styles].length;

                for (i = 0; i < len; i++) {
                    value = states[key][styles][i];

                    if (i === 0) {
                        index = '';
                    } else {
                        index = '_D_' + i; //to identify whether its a duplicate
                    }

                    property = name + index;

                    str += FreiChat.generate_row(name, property, value, '');

                }
            } else {
                value = states[key][styles];
                str_styles += FreiChat.generate_row(name, property, value, '');
                ;

            }

        }

        str_styles += "</tbody></table>";
        str += str_styles;
    }

    if (str === '') {
        str = '<div class="no_display">No states to display!</div>';
    }

    $('#element_states_content').html(str);

    FreiChat.table_changed($('#element_states_content'));

    //dynamic linking here


    for (key in states) {


        for (styles in states[key]) {

            if ($.isArray(states[key][styles])) {
                len = states[key][styles].length;
            } else {
                len = 1;
            }

            name = styles;

            for (i = 0; i < len; i++) {

                if (i === 0) {
                    index = '';
                } else {
                    index = '_D_' + i; //to identify whether its a duplicate
                }

                var ppty = name + index;
                (function(ppty) {

                    $('#tr_state_style_' + ppty).mouseover(function() {
                        $('#state_close_style_' + ppty).css('display', 'inline-block');
                    }).mouseout(function() {
                        $('#state_close_style_' + ppty).hide();
                    });

                    $('#input_state_style_' + ppty).bind('textchange', function(event, previousText) {
                        var state = $('#tr_state_style_' + ppty).parent().parent().prev().children(":first").text();
                        var complete_class = FreiChat.selected_class + state;

                        FreiChat.apply_css('#input_state_style_' + ppty, ppty, complete_class, previousText, true)
                    });

                    $('#state_close_style_' + ppty).click(function() {
                        FreiChat.delete_style(ppty, key, $(this));
                    });
                }(ppty));

            }


        }
    }


    $('#element_states').show();
    $('#actions_style_rules').hide();
    $('#style_rules_body').hide();

}
//-------------------------------------------------------------------------------------
FreiChat.add_new_state_style = function(selected_class, is_state) {

    $('#property_add_state_style').get_value('');
    $('#value_add_state_style').get_value('');

    $('#add_new_state_style_content').show();

    FreiChat.selected_state_class = selected_class;

    setTimeout(function() {
        $('#property_add_state_style').focus();
    }, 100);
}

FreiChat.add_new_state_styles = function(selected_class, is_state) {

    $('#add_state_styles_value').get_value('');

    $('#add_state_styles_textarea').show();

    FreiChat.selected_state_class = selected_class;

    setTimeout(function() {
        $('#add_state_styles_value').focus();
    }, 100);

}
//-------------------------------------------------------------------------------------

FreiChat.add_new_state = function() {

    var
            state = $.trim($('#state_name').get_value()),
            str,
            state_class,
            id;
    state_class = FreiChat.selected_class + state;


    if (state == '') {
        FreiChat.notify('you cannot leave the fields empty');
        return;
    }
    else if (typeof FreiChat.css_array[state_class] != "undefined") {
        FreiChat.notify('state already exists');
        return;
    } else if (state.indexOf(":") == -1) {
        state = " " + state; //fix for child elements
    }

    FreiChat.selected_state_class = state_class;
    str = '<div class="state_label"><div class="state_label_text">' + state + '</div><div onclick=\'FreiChat.add_new_state_styles("' + state_class + '",true)\' class="state_add_new_style_btn btn"> add css</div><div onclick=\'FreiChat.add_new_state_style("' + state_class + '",true)\' class="state_add_new_style_btn btn"> add new style</div></div>';

    id = FreiChat.fix_id(state_class);

    str += '<table id="table_' + id + '" class="table_state_styles"></table>';

    FreiChat.css_array[state_class] = {};
    FreiChat.mod_css_array[state_class] = {};
    $('#add_new_state_div').hide();
    $("#element_states_content").append(str);
    $('#table_' + id).html("No styles to display!");
    $('#state_name').get_value('');
}
//-------------------------------------------------------------------------------------

FreiChat.fix_id = function(id) {
    id = id.replace(".", "");
    id = id.replace(":", "");
    id = id.replace(" ", "");

    return id;
}
//-------------------------------------------------------------------------------------
FreiChat.replace_image = function(config) {

    $('#upload_div').dialog('open').data("data", {
        "fullname": config.name + "." + config.ext,
        "name": config.name,
        "id": config.id,
        "type": config.type
    });

    FreiChat.disable_doc_click = true;

}

FreiChat.file_upload = function() {

    if (FreiChat.allow_upload == false) {
        FreiChat.notify('Please select a file to upload!');
        return;
    }

    var fileInput = document.getElementById('file_input_upload');
    var file = fileInput.files[0];

    // $('#progress_upload_file').html('<div>'+file.name+'&nbsp;<progress id="rep_prg" value=0 max=100></progress><span id="rep_upload_status"></span></div>');

    //var progress=$('#rep_prg');
    //var status = $("#rep_upload_status");
    var xhr = new XMLHttpRequest();
    /* xhr.upload.addEventListener('progress', function(evt){
     
     var percent = evt.loaded/evt.total*100;
     $(progress.selector).get_value(percent);
     
     }, false);*/

    var config = $('#upload_div').data("data");



    xhr.open('POST', 'admin_files/theme_maker/upload.php', true);
    xhr.setRequestHeader("Cache-Control", "no-cache");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader("X-File-Name", file.name);
    xhr.setRequestHeader("X-File-Size", file.size);
    xhr.setRequestHeader("X-File-Type", file.type);
    xhr.setRequestHeader("X-ORIGINAL-FILE-NAME", config.fullname);
    xhr.setRequestHeader("X-TYPE", config.type);
    xhr.setRequestHeader("X-VARIABLE-PHP", config.name);
    xhr.setRequestHeader("Content-Type", "application/octet-stream");
    xhr.onreadystatechange = function() {
        if (xhr.readyState != 4) {
            return;
        }

        if (xhr.responseText == 'exceed') {
            FreiChat.notify('file size has exceeded the allowed limit');
        } else if (xhr.responseText == 'type') {
            FreiChat.notify('invalid file type');
        }
        else {

            var imgid = config.id;

            var type = config.type;
            var path = freidefines.GEN.url;
            path = path + "client/themes/";

            var theme = freidefines.GEN.curr_theme;
            path = path + theme + "/";

            //var newimg = xhr.responseText; remains static
            //freidefines[data.variable_js] = newimg;
            //  alert(data.js_variable + "  " + freidefines[data.js_variable]  + " = " + newimg);


            FreiChat.unsaved_changes = true;
            if (type == 'img') {
                FreiChat.unsaved_changes = true;
                $('#' + imgid).attr('src', path + "images/" + xhr.responseText);
                /*if(imgid != imgid2) {
                 $('#'+imgid2).attr('src',path+newimg);
                 }*/
            }
            /*else{ no bg-images allowed '1=1;--
             $('#'+imgid).css('background-image',"url("+path+newimg+")");
             if(imgid != imgid2) {
             $('#'+imgid2).css('background-image',"url("+path+newimg+")");           
             }
             }*/
        }
        //$('#upload_div').hide();
        $('#file-upload-status').get_value('no image selected yet!');
        FreiChat.allow_upload = false;

    };
    xhr.send(file);

}
//-------------------------------------------------------------------------------------
/*FreiChat.switch_visibility = function(current) {
 var ids = ['themelist_div','new_theme_div','rename_theme'];
 var i=0;
 $('#'+current).slideToggle(); 
 
 for(i=0;i<ids.length;i++){
 if(ids[i]  != current){
 $('#'+ids[i]).hide();
 }
 }    
 }*/
//-------------------------------------------------------------------------------------

FreiChat.notify = function(mesg) {
    if (FreiChat.anim_notify_progress == true && FreiChat.mesg == mesg)
        return;

    if (FreiChat.anim_notify_progress == true && FreiChat.mesg != mesg) {
        $('#notification').html(mesg);
        FreiChat.mesg = mesg;
        FreiChat.notify_disappear_time = FreiChat.notify_disappear_time + 3000;
        return;
    }

    FreiChat.anim_notify_progress = true;
    $('#notification').html(mesg).center().slideDown().css('top', '0px').click(function() {
        $('#notification').slideUp();
    }).delay(FreiChat.notify_disappear_time).fadeOut(function() {
        FreiChat.anim_notify_progress = false;
    });
    FreiChat.mesg = mesg;
}
//-------------------------------------------------------------------------------------

FreiChat.restore_defaults = function() {
    var value = confirm('This will undo all your work to your last save , \nAre you sure ?');
    if (value == true) {
        //        $.get("admin_files/theme_maker/theme_maker.php?action=restore",function(data){
        FreiChat.unsaved_changes = false; //to prevent popup
        window.location.reload(true);
        //      });

    }
}
//-------------------------------------------------------------------------------------

FreiChat.save_theme = function() {

    var value = confirm('Once saved , your changes will become permanent , \nAre you sure ?');

    FreiChat.save_style_changes();

    if (value == true) {
        $.getJSON("admin_files/theme_maker/theme_maker.php?action=save", function(data) {
            if (data === 'success') {
                FreiChat.unsaved_changes = false;
                FreiChat.notify('Current changes have been saved');
            } else {
                FreiChat.notify('Could not save changes...');
            }

        }, 'json');
    }
}
//-------------------------------------------------------------------------------------
FreiChat.switch_button = function(mode_key, mode_value, first) {


    /*if(sessionStorage[mode_key] == mode_value && typeof first == "undefined"){
     return;
     }
     
     sessionStorage[mode_key] = mode_value;
     $('#'+mode_value).prop('checked', true);     
     
     
     if(mode_value == "parameters") {
     FreiChat.get_css_[);
     $('#upload_div').dialog('close');
     }else if(mode_value == 'image') {
     $( "#style_rules" ).dialog('close');
     }else if(mode_value == 'chat'){
     $('#chat_switch_div').show('slow');
     $('#chatroom_switch_div').hide();
     $('#upload_div').dialog('close');
     $( "#style_rules" ).dialog('close');
     }else if(mode_value == 'chatroom') {
     $('#chat_switch_div').hide();
     $('#chatroom_switch_div').show('slow');
     $('#upload_div').dialog('close');
     $( "#style_rules" ).dialog('close');
     }*/

    //FreiChat.get_css_[); //update css Array 

}
//-------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------
FreiChat.get_css_array = function() {

    $.getJSON("admin_files/theme_maker/theme_maker.php?action=get_css_array",
            {
            }, function(data) {
        console.log(data);
        FreiChat.css_array = data;
    }, 'json');

}
//-------------------------------------------------------------------------------------
FreiChat.save_style_changes = function() {

    var filename;
    if (sessionStorage.freichat_switch == 'chat') {
        filename = 'styles.css';
    } else
    {
        filename = 'styles.css';
    }


    $.post('admin_files/theme_maker/theme_maker.php?action=save_style_changes',
            {
                "css_array": FreiChat.mod_css_array,
                filename: filename

            }, function(data) {
        //console.log(data);
    });
}

FreiChat.set_default = function() {

    var current_theme = freidefines.GEN.curr_theme

    $.post('admin_files/theme_maker/theme_maker.php?action=set_default',
            {
                current_theme: current_theme
            }, function(data) {
        FreiChat.notify(current_theme + " has been succesfully set as the default theme !");
    });

}
/*------------------------------------------------------------------------------------*/
FreiChat.show_tab_content = function(el) {

    var el_o, el_li, el_li_o;

    if (el == 'rooms') {
        el = $jn('#frei_roompanel');
        el_o = $jn('#frei_userpanel');
        el_li = $jn('#frei_roompanel_li');
        el_li_o = $jn('#frei_userpanel_li');
    } else {
        el = $jn('#frei_userpanel');
        el_o = $jn('#frei_roompanel');
        el_li = $jn('#frei_userpanel_li');
        el_li_o = $jn('#frei_roompanel_li');
    }


    if (el.is(":visible")) {
    } else {
        el_o.hide();
        el.show('clip');
        el_li.addClass('frei_chatroom_tabs_selected');
        el_li_o.removeClass('frei_chatroom_tabs_selected');
    }

};
/*------------------------------------------------------------------------------------*/
FreiChat.add_users = function() {

    var names = ["Sharon Davis", "Celestine Vega", "Lawrence Sebring", "Beatrice Vanmeter", "Corey Fox", "Ruth Struck"];
    var str = "";

    var i = 0;
    for (i = 0; i < names.length; i++)
        str += "<div id='freichat_user_" + i + "' title='I am available' class='freichat_userlist'>\n\
                        <span>\n\
                            <span style='display:block' class='freichat_userscontentavatar'>\n\
                        <img src='http://www.gravatar.com/avatar/" + md5(names[i]) + "?s=24&amp;d=wavatar' class='freichat_userscontentavatarimage' alt='avatar'></span>\n\
                        </span>\n\
                        <span class='freichat_userscontentname'>" + names[i] + "</span>\n\
                        <span>&nbsp;<img class='freichat_userscontentstatus'  src='" + FreiChat.make_url("images/onlineimg.png") + "' height='12' width='12' alt='status'></span>\n\
                    </div>"



    var height;
    $('#frei').html(str);
    height = parseInt($('#freichat_user_0').height() + 1) * (i);
    $("#frei").css("height", height);
    $('#frei_user_count').html(i);

}
/*-----------------------------------------------------------------------------------*/
FreiChat.hide_all_elements = function() {
    var parents = ["freichat", "frei_options", "freichat_chatbox", "chatwindow_minimized", "chatwindow_maximized", "freicontain1", "frei_chatroom", "onfreioffline"];
    var len = parents.length;

    for (var i = 0; i < len; i++) {
        $("#" + parents[i]).hide();
    }
    /*  
     $("#freicontain10").show();
     $("#frei_options").show();
     $('#freichat_chatbox').show();
     $('#freicontain1').show();
     $('#frei_chatroom').show();
     */
    FreiChat.last_element_shown = false;


    $('#t_chatwindow_min').click(function() {
        FreiChat.switch_element(['chatwindow_minimized']);
    })

    $('#t_chatwindow_options').click(function() {
        FreiChat.switch_element(['freicontain1']);
    })

    $('#t_freichat').click(function() {
        FreiChat.switch_element(['freichat_chatbox', 'freichat']);
    })

    $('#t_chatwindow_max').click(function() {
        FreiChat.switch_element(['chatwindow_maximized']);
    })

    $('#t_chatroom').click(function() {
        FreiChat.switch_element(['frei_chatroom']);
    })

    $('#t_frei_options').click(function() {
        FreiChat.switch_element(['frei_options', 'freichat']);
    })

    $('#back_to_list').click(function() {

        FreiChat.return_to_elements();

        //hide all current elements

        for (var i = 0; i < FreiChat.last_element_shown.length; i++)
            FreiChat.last_element_shown[i].hide();
        FreiChat.last_element_shown = false; //no element is now currently visible
        $(this).hide();

        $('#t_blocks').css("visibility", "visible").animate({
            opacity: 1
        }, '1000', 'linear', function() {

        });

        $('#t_grid').hide();
    })

}

FreiChat.switch_element = function(id) {

    //let the first element be the current elements forget the rest
    element_zoomed = id[0];

    $('#style_rules_description').html('<div style="text-align:center;">please click on any element to edit it</div>');

    var i = 0;

    //hide the previous elements
    if (FreiChat.last_element_shown) {
        for (i = 0; i < FreiChat.last_element_shown.length; i++)
            FreiChat.last_element_shown[i].hide();
    } else {
        FreiChat.last_element_shown = [];
    }

    //store the current elements    
    for (i = 0; i < id.length; i++)
        FreiChat.last_element_shown[i] = $('#' + id[i]);


    //hide all the image blocks    
    $('#t_blocks').animate({
        opacity: 0
    }, '1000', 'linear', function() {
        $(this).css("visibility", "hidden");
    });

    //show all the current elements
    for (i = 0; i < FreiChat.last_element_shown.length; i++)
        FreiChat.last_element_shown[i].show();

    //show the back button
    $('#back_to_list').show();

    //show the grid image behind
    $('#t_grid').show();
}
/*------------------------------------------------------------------------------------*/
FreiChat.show_hints = function() {

    var hints =
            ["WYSIWYG theme maker V4.0",
                "change the theme under general settings for your changes to reflect in your website",
                "press &uarr; , &darr; to increment or decrement values by 1",
                "press shift+&uarr; , shift+&darr; to increment or decrement values by 10",
                "all your themes are stored in freichat/client/themes directory",
                "you can directly edit .LESS files under theme directory and compile them",
                "if changes do not reflect check permissions of freichat/client/themes directory",
                "you can press esc to close dialogs",
                "all icons are stored in freichat/client/themes/" + freidefines.GEN.curr_theme + "/icons directory"
            ]

    var max = hints.length - 1, min = 0;
    var theme_hint = $("#theme_hint");

    theme_hint.html("WYSIWYG theme maker V4.0");

    setInterval(function() {
        random_no = Math.floor(Math.random() * (max - min + 1)) + min;
        hint = hints[random_no];
        theme_hint.html(hint);

    }, 7000);
};
//--------------------------------------------------------------------------------------

(function($) {


    $.fn.get_value = function(val) {


        if (this.is("input") || this.is("textarea")) {
            if (typeof val != "undefined") {
                this.val(val);
            } else {
                return  this.val();
            }
        }

        if (typeof val != "undefined") {
            this.text(val);
        } else {
            return this.text();
        }
    };

}(jQuery));
