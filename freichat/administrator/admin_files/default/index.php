<style type="text/css">

    .info_circ{
        margin: 10px 10px 5px 9px;
        position: relative;
        display: inline-block;
        text-align: center;
        border: 1px solid #DFDFDF;
        background: #E9E9E9;
        background: -moz-linear-gradient(top, #E9E9E9 0%, #EAEAEA 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#E9E9E9), color-stop(100%,#EAEAEA));
        background: -webkit-linear-gradient(top, #E9E9E9 0%,#EAEAEA 100%);
        background: -o-linear-gradient(top, #E9E9E9 0%,#EAEAEA 100%);
        background: -ms-linear-gradient(top, #E9E9E9 0%,#EAEAEA 100%);
        background: linear-gradient(top, #E9E9E9 0%,#EAEAEA 100%);
        width: 98%;
        height: 160px;
        box-shadow: inset 0 2px 0px #CCC, 0 1px 2px white;
        -webkit-box-shadow: inset 0 2px 0px #CCC, 0 1px 2px white;
        -moz-box-shadow: inset 0 2px 0px #ccc, 0 1px 2px #fff;
        border-radius: 50%/10%;
        -moz-border-radius: 50%/10%;
        -webkit-border-radius: 50%/10%;
    }

    .info_circ_content {
        margin-right: 40px;
        display:inline-block;
        background: #FAFAFA;
        background: -moz-linear-gradient(top, #FAFAFA 0%, #DFDFDF 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#FAFAFA), color-stop(100%,#DFDFDF));
        background: -webkit-linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        background: -o-linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        background: -ms-linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        background: linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fafafa', endColorstr='#dfdfdf',GradientType=0 );
        border: 1px solid #DFDFDF;
        transition: all 0.1s ease-in-out;
        -moz-transition: all 0.1s ease-in-out;
        -webkit-transition: all 0.1s ease-in-out;
        width: 100px;
        height: 100px;
        box-shadow: 0 2px 3px #B5B5B5, 0px 1px 0 white inset;
        -webkit-box-shadow: 0 2px 3px #B5B5B5, 0px 1px 0 white inset;
        -moz-box-shadow: 0 2px 3px #b5b5b5, 0px 1px 0 #fff inset;
        border-radius: 50%;
        -moz-border-radius: 50%;
        -webkit-border-radius: 50%;
        margin-top:12px;
    }

    .info_circ_content:hover{
        background: #E6E6E6;
        background: -moz-linear-gradient(top, #F9F9F9 0%, #E6E6E6 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#F9F9F9), color-stop(100%,#E6E6E6));
        background: -webkit-linear-gradient(top, #F9F9F9 0%,#E6E6E6 100%);
        background: -o-linear-gradient(top, #F9F9F9 0%,#E6E6E6 100%);
        background: -ms-linear-gradient(top, #F9F9F9 0%,#E6E6E6 100%);
        background: linear-gradient(top, #F9F9F9 0%,#E6E6E6 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f9f9f9', endColorstr='#e6e6e6',GradientType=0 );
    }


    .info_circ_text {
        font-weight: bold;
        color: #666;
        margin-top: 42%;
        text-align: center;
    }

    .info_circ_desc {
        background: #FAFAFA;
        background: -moz-linear-gradient(top, #FAFAFA 0%, #DFDFDF 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#FAFAFA), color-stop(100%,#DFDFDF));
        background: -webkit-linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        background: -o-linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        background: -ms-linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        background: linear-gradient(top, #FAFAFA 0%,#DFDFDF 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fafafa', endColorstr='#dfdfdf',GradientType=0 );
        display: inline-block;
        position: relative;
        padding: 4px;
        top: 48px;
        right: 12px;
        width: 110px;
        border-radius: 5px;
    }


</style>

<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-road"></i> Overview</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">

                <div class="info_circ">

                    <div class="info_circ_content">
                        <div class="info_circ_text" id="total_messages_circ">
                           <!-- <img src="img/ajax-loaders/ajax-loader-1.gif" title="img/ajax-loaders/ajax-loader-1.gif"> -->
                        </div>
                        <div class="info_circ_desc">Total messages</div>

                    </div>





                    <div class="info_circ_content">
                        <div class="info_circ_text" id="online_users_circ">
                          <!--  <img src="img/ajax-loaders/ajax-loader-1.gif" title="img/ajax-loaders/ajax-loader-1.gif">  -->                           
                        </div> 
                        <div class="info_circ_desc">Online users</div>

                    </div>


                    <div class="info_circ_content">
                        <div class="info_circ_text" id="banned_users_circ">
                          <!--  <img src="img/ajax-loaders/ajax-loader-1.gif" title="img/ajax-loaders/ajax-loader-1.gif"> -->                         
                        </div> 
                        <div class="info_circ_desc">Banned users</div>

                    </div>


                    <div class="info_circ_content">
                        <div class="info_circ_text">
                            9.5
                        </div>                        
                        <div class="info_circ_desc">Current version</div>

                    </div>


                    <div class="info_circ_content">
                        <div class="info_circ_text" id="latest_ver_img">
                        </div>                        
                        <div class="info_circ_desc">Latest version</div>

                    </div>


                </div>

                <br/>

            </div>                   
        </div>
    </div><!--/span-->
</div>


<script type="text/javascript">

    $(document).ready(function() {

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();
        var u = "" + dd + mm + yyyy;
        var image = '<img src="http://codologic.com/page/latest_ver.png?u=' + u + '" style="display: inline;height:10px"/><br/>';
        $('#latest_ver_img').html(image);
    });



    var correct_large_nos = function(vals) {
        var large_no = 999999999999;
        var ids = ["online_users_circ", "total_messages_circ", "banned_users_circ"];

        var len = ids.length - 1;
        var ele, no;

        while (len >= 0) {
            ele = document.getElementById(ids[len]);
            no = parseInt(vals[len]);

            if (no > large_no) {
                no = ">" + large_no;
            }

            ele.innerHTML = no;

            len--;
        }

    };

    var get_values = function() {

        $.getJSON("admin_files/default/data.php?data_mode=get_values", function(values) {
            correct_large_nos(values);
        }, 'json');
    }();



</script>


<div class="box">
    <div class="box-header well">
        <h2><i class="icon-list-alt"></i> Message Statistics</h2>

    </div>
    <div class="box-content">
        <div id="msgstat"  class="center" style="height:300px" ></div>
        <p id="hoverdata">Mouse position at (<span id="x">0</span>, <span id="y">0</span>). <span id="clickdata"></span></p>
    </div>
</div>

<script>

    function get_sql_date(millisecond) {


        var myDate = new Date(millisecond);
        var myDate_string = myDate.toISOString();
        var myDate_string = myDate_string.replace("T", " ");
        var myDate_string = myDate_string.substring(0, myDate_string.length - 5);

        return myDate_string;
    }


    $(window).load(function() {

        if ($("#msgstat").length)
        {
            var from_day = new Date().getTime() - (10 * 24 * 60 * 60 * 1000);
            var to_day = new Date().getTime() + (3 * 24 * 60 * 60 * 1000)
        }
        ;



        $.getJSON("admin_files/default/data.php?data_mode=get_mesg_stats",
                {from_day: get_sql_date(from_day), to_day: get_sql_date(to_day)},
        function(data) {

            // data=JSON.parse(data);

            var sin = [], cos = [];

            var len = data.length - 1;
            var i = 0;

            var max = 0, on_day, msg_count;

            while (len >= 0) {

                on_day = parseInt(data[i].on_day);
                msg_count = parseInt(data[i].msg_count);

                sin.push([on_day, msg_count]);

                if (max < msg_count) {
                    max = msg_count;
                }
                i++;
                len--;

            }

            /*if(sin.length == 0){
             var d = new Date();
             sin.push([d.getTime(),0]);
             sin.push([from_day,0]);
             
             }
             
             
             for(i=0;i<sin.length;i++) {
             if(sin[i][0] < from_day)
             sin.push([from_day,0]);
             
             }*/


            var delta = 0.2 * max; //20%

            if (max < 5) {
                max = 4;
                delta = 1;
            }

            max = max + delta;
            max = parseInt(max);

            var plot = $.plot($("#msgstat"),
                    [{data: sin, label: "No of messages"}], {
                series: {
                    lines: {show: true},
                    points: {show: true}
                },
                grid: {hoverable: true, clickable: true, backgroundColor: {colors: ["#fff", "#eee"]}},
                yaxis: {min: 0, max: max},
                xaxis: {mode: "time", timeformat: "%m/%d/%y",
                    /*minTickSize: [1, "day"],*/
                    min: from_day,
                    max: to_day},
                colors: ["#539F2E", "#3C67A5"]
            });

            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 5,
                    border: '1px solid #fdd',
                    padding: '2px',
                    'background-color': '#dfeffc',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }

            var previousPoint = null;
            $("#msgstat").bind("plothover", function(event, pos, item) {
                var time = new Date(pos.x);
                var theyear = time.getFullYear();
                var themonth = time.getMonth() + 1;
                var thedate = time.getDate();
                var string = themonth + "/" + thedate + "/" + theyear;

                $("#x").text(string);

                var y_data = pos.y;
                if (y_data < 0) {
                    y_data = 0;
                }
                $("#y").text(parseInt(y_data));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1];//.toFixed(2);console.log(x);
                        var time = new Date(item.datapoint[0]);
                        var theyear = time.getFullYear();
                        var themonth = time.getMonth() + 1;
                        var thedate = time.getDate();
                        var string = themonth + "/" + thedate + "/" + theyear;
                        showTooltip(item.pageX, item.pageY,
                                item.series.label + " on " + string + " is " + y);
                    }
                }
                else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });



            $("#msgstat").bind("plotclick", function(event, pos, item) {
                if (item) {
                    $("#clickdata").text("You clicked point " + (item.dataIndex + 1) + " in " + item.series.label + ".");
                    plot.highlight(item.series, item.datapoint);
                }
            });
        });

    });



</script>













