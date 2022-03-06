/**
# Author :  Carlos Galindo
# Website: cjax.net
# Date :  2008 3:56:38 AM
# Copyright 2008
**/

/**
 Hello_world application,  a desmotration starting point for creating a CJAX addon.
**/

/**
 all parameters will be referenced by "param" and the order number,
 for example, the first the  parameter will be accessed  as  param1 seconds parameter params2 and so on
 on the your contructor function there can be available as many paramenter as the amouth of parameter that were specified
 in the php function call. Notice that you can have as many parameters as you like to have.

 the name of the addon function must be the same as the way is called from php, and the file name for the javascript file
 must  be the same as well

 **/

function hello_world(params)
{
    //  we call this function from the php prospective as follow
    // inclucde 'cjax/cjax.php';
    // $CJAX->init(true);
    // $CJAX->hello_world('param1 here','param2 !');
    // and bellow is a how you would access these parameters.
    //get a parameter value
    var param = params.xml('param1');
    alert('HELLO_WORLD PLUGIN (string parameter): ' + param);
    // -- OR --
    //get an array with parameters
    //this will overwrite the last statement
    var param = params.array();
    alert('HELLO_WORLD PLUGIN (from array): ' + param[0]);

}