{* 7.4beta1-17-g4339df0 *}
{assign var='item' value='listing_charge_by_word'}

<div class="{cycle values="row_color1,row_color2"}">
	<div class="leftColumn">Enabled</div>
	<div class="rightColumn">
		<input type="checkbox" value="1" name="{$item}[enabled]" {if $enabled}checked="checked"{/if} onclick="if(this.checked) $('{$item}_settings').show(); else $('{$item}_settings').hide();" />
	</div>
	<div class="clearColumn"></div>
</div>

<div id="{$item}_settings" {if !$enabled}style="display: none;"{/if}>

	<div class="{cycle values="row_color1,row_color2"}">
		<div class="leftColumn">Charge Type</div>
		<div class="rightColumn">
			<input type="radio" name="{$item}[charge_type]" value="1" {if $charge_type == 1}checked="checked"{/if} onclick="if(this.checked) $('count_whitespace').hide();" /> charge by word<br />
			<input type="radio" name="{$item}[charge_type]" value="2" {if $charge_type == 2}checked="checked"{/if} onclick="if(this.checked) $('count_whitespace').show();" /> charge by character<br />
				<span id="count_whitespace" {if $charge_type == 1}style="display: none;"{/if} ><input type="checkbox" name="{$item}[count_whitespace]" value="1" style="margin-left: 20px;" {if $count_whitespace}checked="checked"{/if} /> count whitespace</span>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values="row_color1,row_color2"}">
		<div class="leftColumn">Pricing</div>
		<div class="rightColumn">
			New listings: {$pre}<input type="text" name="{$item}[word_cost]" value="{$word_cost}" size="4" />{$post} per word/character<br />
			Renewals: {$pre}<input type="text" name="{$item}[renewal_cost]" value="{$renewal_cost}" size="4" />{$post} per word/character
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values="row_color1,row_color2"}">
		<div class="leftColumn">Count words/characters in</div>
		<div class="rightColumn">
			<input type="checkbox" name="{$item}[count_title]" {if $count_title}checked="checked"{/if} value="1" /> title<br />
			<input type="checkbox" name="{$item}[count_description]" {if $count_description}checked="checked"{/if} value="1" /> description<br />
			<input type="checkbox" name="{$item}[count_optionals]" {if $count_optionals}checked="checked"{/if} value="1" /> optional fields<br />
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values="row_color1,row_color2"}">
		<div class="leftColumn">Free words/characters</div>
		<div class="rightColumn">
			The first <input type="text" name="{$item}[skip_words]" value="{$skip_words}" size="2" /> words/characters are free			
		</div>
		<div class="clearColumn"></div>
	</div>

</div>