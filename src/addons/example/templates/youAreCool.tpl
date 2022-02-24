{* b1a8dfa *}

{*
	This template displays the mainbody for the "youAreCool" step, when
	adding an eWidget to the cart.  Sure the contents are a little cheeky,
	but this is just an example anyways, might as well have fun when
	demonstrating how to do things in addons!

	By the way, you get bonus points for actually taking a look at the template
	file to see what's under the hood.  :-)
*}

{include file="system/cart/cart_steps.tpl"}

{foreach from=$error_msgs item=err_msg}
	<div class="cart_error">
		{$err_msg}
	</div>
{/foreach}

<h1>Wow, you are SO Cool!</h1>

<p>Not many people are cool enough to buy an <strong>eWidget</strong>, which
happens to be the coolest item to buy
in this example addon!  And the <em>only</em> thing to buy in this example addon,
come to think of it...</p>

<p>By the way, the cost of this thing will put you back {$price|displayPrice}.</p>
<br />
{*
 NOTE:  $process_form_url is one of the variables automatically set
 using $cart->getCommonTemplateVars()
*}
<form action='{$process_form_url}' method='post'>
	First though, just to confirm you are cool enough to purchase an eWidget, are you:<br />
	<label><input type="radio" name="eWidget[cool_or_not]" value="soCool"{if $cool_or_not == 'soCool'} checked="checked"{/if} /> SO <span style="color: cyan;">Cool</span></label><br />
	<label>
		<input type="radio" name="eWidget[cool_or_not]" value="notSoCool"{if $cool_or_not == 'notSoCool'} checked="checked"{/if} />
		<em style="color: red;">NOT</em> SO <span style="color: cyan;">Cool</span>
	</label>
	<br /><br />
	<input type="submit" value="Next &gt;" class="button" />
</form>
<br /><br />
<p>Not feeling cool enough?  That's ok, we understand, just cancel your eWidget order with the
button below.</p>
<a href="{$cancelButtonUrl}" class="cancel">Cancel</a>
