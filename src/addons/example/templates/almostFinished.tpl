{* b1a8dfa *}

{*
	This template displays the mainbody for the "almostFinished" step, when
	adding an eWidget to the cart.
*}

{include file="cart_steps.tpl" g_resource="cart" g_type="system"}

{foreach from=$error_msgs item=err_msg}
	<div class="cart_error">
		{$err_msg}
	</div>
{/foreach}

<h1>Almost Finished!!!</h1>

<p>This step is here purely to tell you you are almost finished adding an
eWidget to your cart.  You would actually be done by now with the process of
adding a fresh brand new eWidget to the cart, but we wanted to display
this step to inform you that you are actually almost finished, and that you are not,
in fact, finished, but that is purely because we took the time to tell you of
this fact.</p>

<p>It's also here to demonstrate you can add as many different steps as needed
to add your order item to the cart.  They don't even have to collect information
if you don't want them to!</p>
{*
 NOTE:  $process_form_url is one of the variables automatically set 
 using $cart->getCommonTemplateVars()
*}
<a href="{$process_form_url}" class="button">Finish &gt;</a>
<br /><br />
<p>Did you change your mind already?  Don't worry you can still cancel, just use
the button below.</p>
<a href="{$cancelButtonUrl}" class="cancel">Cancel</a>


