<?php

/**
#   Date: 9/21/2007
#   Author: Carlos Galindo
#   Last modified:  @LASTMODIFIED@
**/

define('IN_SAMPLES', 1);

/**
 * include cjax.php which is inside the CJAX main directory
 */
include '../cjax.php';

if (isset($_GET['login'])) {
    $username = $CJAX->get('username');
    $password = $CJAX->get('password');

    $new = $CJAX->get('new_sample');



    if ($username == '') {
        if (isset($new) && $new) {
                $html = ("<div class='invalidate_message'>
						This field is required!!!!
					</div>");

            $CJAX->invalidate('username', $html);
        } else {
            $CJAX->invalidate('username', 'This field is required!!!!');
        }
    } elseif ($password == '') {
        if (isset($new) && $new) {
            $html = ("<div class='invalidate_message'>
						This field is required too!!!!
					</div>");

            $CJAX->invalidate('password', $html);
        } else {
            $CJAX->invalidate('password', 'This field is required too!!!!');
        }
    }

    if ($password != '' && $password != '') {
        $CJAX->alert("You have typed something.. in both fields. This sample is to demonstrate how to use invalidate functionality; also note that there 0 is javascript coding!! you only do PHP!  We used \$CJAX->addEventTo() to allow more interaction with the input fields");
    }
    flush();
    exit();
}

$username = $CJAX->value('username');
$password = $CJAX->value('password');





?>
<HTML>
<HEAD>
<?php




echo $CJAX->init();

if (!$CJAX->get('new_sample')) {
    $call = $CJAX->call("?login=1&username=$username&password=$password&new_sample=1");



    $html = ("<div class='invalidate_message'>
						This field is required!!!!
					</div>");

    $html2 = ("<div class='invalidate_message'>
						This field is required too!!!!
					</div>");

    $CJAX->addEventTo("username", "click", "CJAX.invalidate('username','{$CJAX->encode($html)}')");
    $CJAX->addEventTo("password", "click", "CJAX.invalidate('password','{$CJAX->encode($html2)}')");


    ?>
<style>

body{
margin:0;
padding:0;
background-color:#E8E8E8;
min-width: 900px;
}
.invalidate
{
    position:absolute;
    width:300px;
    left:50%;
    margin-left:-150px;
    height:auto;
    text-align:right;
    background-color:#E8E8E8;
    padding-left:50px;
    margin-right:50px;
}
.invalidate label{
    font-family: verdana;
    font-color:#7F7F7F;
    font-size:12px;
    text-align:right;
    
}

.invalidate_message{
    position:relative;
    color:#454545;
    background-image: url(images/invalidate.gif);
    background-repeat: no-repeat;
    padding:11px;
    width:255px;
    height: 90px;
    margin-top:-30px;
    left:-20px;
    padding-top:30px;
    padding-left:45px;
    font-family: verdana;
    font-size:10px;
}

.inputs{
    border-style:solid;
    border-color:#000000;
    border-width:1px;
}
</style>
</HEAD>
<BODY>



<br />
<br />
<br />
<div class='invalidate'>
<label class='label'>Username:</label><input class='inputs' type='text' id='username' value=''>
<br />
<label class='label'>Password:</label><input class='inputs' type='password' id='password' value=''>
<br />
<!--   <input type='button' value='Login' <?php echo $call;?>> -->
<br /> <br />
</div>


<br /><br /><br />

<div style='position:relative;width:800px; font-family:tahoma;fon-size:11px;margin-left:40px;'>
(it is recommended that you read the explanation in order to understand how this works)<br /><br />
<b>Explanation:</b><br />
When you click on the text inputs above, CJAX sends an XmlHttpRequest call to the server, which is carefully
proceed by the CJAX engine including extra instructions to handle any possible event.
Then when the call arrives to the server, the developer is able to access any data that has been sent
throght the call, in this case, the username and password.<br />
you can access the variable by using the next statement:<br />
 <br />
 <b>
 $username = $CJAX->get('username');
 </b>
 <br />
 now we have the variable <b><i>$username</i></b> with the value of whatever the user has introduced into that text field.
 <br/> now that you have that information you could check to make sure that user name is valid.<br />
 in this example we check by doing this:<br />
 <b>
 <pre>
 if($username == '')
 {
    //
 }
 </pre>
 </b>
 which is <b>only</b> for demonstration purpose. Probably you don't want to use that kind of  security check in your application for authentification,
  you migh  like to insert a secure username check there.
 <br /> now if PHP sees that variable username is equals to ''  or in other words, is empty.
 <br /> so there is where you might want to throw an error to user, so that he can check his input,
 
 there is where the invalidate function comes quite handy.<br />
 <b>$CJAX->invalidate('username','This field is required!!!!');</b>
 <br />

<br />
CLick on the button and see how the invalidate function works, thus  then you just need
to call it through the backend and phew!, validate any field, without any extra effort.
just remember to specify the id of the element. 
for example,  <b>$CJAX->invalidate("username");</b> <br />
notice how the text field gets automaticly highlighted when you click on the button, and if you click again, then an alert
message will be shown with the same error message, which can be customized, if you do type someting then error will
go away.
<br />
get it working 
by adding an extra parameter, just like this <br /><b>$CJAX->invalidate("username","PLEASE ENTER THIS INFO!");</b> you will
have a custom error.<br />
<br />
but the awesome part about this function is that you  really can customize the error message, by adding
extra CSS and HTML. and turn it into something apealing to your expectations. create an arrow, or something like that.
<br /> this function is not to check for authentification specifically<br />
errors, it is for everyday use as you can use it in all your applications to validation any input field.

</div>

<span id='code'></span>
</BODY>
</HTML>

    <?php
} else {
    $call = $CJAX->call("?login=1&username=$username&password=$password");
    ?>
    
</HEAD>
<BODY>
<br />
<label>Username:</label><input type='text' id='username' value=''>
<br />
<label>Password:</label><input type='password' id='password' value=''>
<br />
<input type='button' value='Login' <?php echo $call;?>>
<br /> <br />

    <?php
}

?>