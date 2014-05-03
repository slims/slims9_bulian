<?php
require 'header.php';
?>
<div> 

    <div class="box span10 centerme" id="content">



        <div class="box-header well">
            <h2>freichat smart custom installation - step 3</h2>
        </div>

        <div class="box-content" >
            <div class="page-header">
                <h1>User Authentication</h1>
            </div>

            <div class="well">

                <em>
                    Here, you need to pass the userid of the current logged in user to freichat 
                    And if the user is a guest you will have to pass null to freichat

                    <br/><br/>
                    If you think the below process is way too complicated you can skip this step 
                    but freichat will then work in guest-only mode
                    <button style="display: block"class="btn" id="skip_integration"><i class="icon-minus-sign"></i> I want to skip it</button>
                </em>

            </div>


            <div class="well">
                Lets look at an example

                <div style="margin-top:10px" class="example_img">
                    <div>Following is a table structure of a users table</div> <br/>
                    <img style="border:1px solid rgba(0, 0, 0, 0.15)" src="images/user_db.png" />
                </div>

                <div class="spaceout">You need to tell FreiChat the <b>id</b> (i.e the userid) of the current user</div>
                <div style="margin-top:10px" class="disabled">
                    <div style="margin-top:20px"> Lets look at an example code for it </div>

                    <div class="spaceout">


                                <pre class="frei_ident_cookieless hideme">
<code data-language="php">if(USER_IS_LOGGED_IN)
{ 
    $ses = LOGGED_IN_USERID //tell freichat the userid of the current user

    setcookie("freichat_user", "LOGGED_IN", time()+3600, "/"); // *do not change -> freichat code
}
else {
    $ses = null; //tell freichat that the current user is a guest

    setcookie("freichat_user", null, time()+3600, "/"); // *do not change -> freichat code
} </code></pre>
                                <pre class="frei_ident_php_cookie hideme">
<code data-language="php">if(USER_IS_LOGGED_IN)
{ 
    setcookie("freichat_user", LOGGED_IN_USERID, time()+3600, "/"); // tell freichat the current users' id
}
else {
    setcookie("freichat_user", null, time()+3600, "/"); // tell freichat the current user is guest
}</code></pre>

                        
                                <pre class="frei_ident_csharp_cookie hideme">
<code data-language="c#">if(USER_IS_LOGGED_IN)
{ 
Response.Cookies["freichat_user"].Value = LOGGED_IN_USERID ;// tell freichat the current users' id
}
else {
Response.Cookies["freichat_user"].Value = null; // tell freichat the current user is guest
}
 </code></pre>

                        </div>
                    </div>
                </div>



            <div class="well">
                <span class="frei_ident_cookieless hideme">
                You need to edit above code in such a way that the userid is stored in the <b><span style="color:brown">$ses</span></b> variable of the current user
                and null if the user is a guest
                <br/>Then replace <b><span style="color:brown">$ses=0;</span></b> in your copy-pasted code with your edited code
                </span>
                <span class="frei_ident_csharp_cookie hideme">
                You need to edit above code in such a way that the userid of the current user is stored in the <b><span style="color:brown">freichat_user</span></b>cookie
                and null if the user is a guest
                <br/>Then replace <b><span style="color:brown">Response.Cookies["freichat_user"].Value = null; // tell freichat the current user is guest</span></b> in your copy-pasted code with your edited code
                </span>

                <span class="frei_ident_php_cookie hideme">
                You need to edit above code in such a way that the userid of the current user is stored in the <b><span style="color:brown">freichat_user</span></b>cookie
                and null if the user is a guest
                <br/>Then replace <b><span style="color:brown">setcookie("freichat_user", LOGGED_IN_USERID, time()+3600, "/"); // tell freichat the current users' id</span></b> in your copy-pasted code with your edited code
                </span>

                
                <div class="spaceout">
                    <button id="done_secondary_paste" class="btn btn-primary"><i class="icon-ok icon-white"></i> Done</button>
                    <span class="muted">Hit done after you finish editing the copy-pasted code </span>
                </div>
            </div>


            <div class="frame_load hideme well" id="myframe2_div">
                <iframe  id="myframe2" style="width:100%" height="400px" src="" seamless></iframe>
            </div>

            <div class="well hideme" id="steps">
                <div class="spaceout">Now, please login to your website </div>
                <div class="spaceout">
                    <button id="done_login" class="btn btn-primary"><i class="icon-ok icon-white"></i> Done</button>
                    <span class="muted">click me once you have logged in your website </span>
                </div> 

            </div>

            <div id="check_user" class="well hideme" >

                <div class="alert alert-info" id="login_notice">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div>verifying your user authentication code</div> <!-- why waste a name on id -->
                </div>
                <div id="reload_frame_option2">
                    <div class="muted"> If you have made any changes , you can reload the frame at anytime by clicking reload button below </div>
                    <button style="margin-top:10px" id="frame_close" class="btn">reload</button>
                </div>
            </div>

            <div class="well hideme" id="success_integration">

                Congratulations on integrating your website user login system with FreiChat!

                <button style="display:block" class="spaceout btn btn-primary" id="integration_success_btn"><i class="icon-arrow-right icon-white"></i> Let's proceed</button>
            </div>

            </div>
            <!-- content ends here -->
        </div>

    </div>





    <div class="hideme" id="function_calls">smart_integration()</div>
</div>




<?php
/*
  <script>


  //i wont be executed on ajax call  ;)
  document.location = 'smart.php';
  </script>

 */

require 'footer.php';