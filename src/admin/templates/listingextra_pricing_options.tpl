{* 7.4.2-5-g54f4d97 *}
<div class="col_hdr" id="{$name.internal}_section">{$name.friendly}</div>
<div class='row_color1'>
	<div class="leftColumn">
		Use {$name.friendly}
	</div>
	<div class="rightColumn">
		<input type="radio" name="extras[{$name.internal}][use]" value="1" {if $values.use}checked="checked" {/if} onclick="if(this.checked)jQuery('#{$name.internal}_priceOptions').show();" /> yes<br />
		<input type="radio" name="extras[{$name.internal}][use]" value="0" {if !$values.use}checked="checked" {/if} onclick="if(this.checked)jQuery('#{$name.internal}_priceOptions').hide();" /> no
	</div>
	<div class="clearColumn"></div>
</div>

<div id="{$name.internal}_priceOptions" {if !$values.use}style="display:none;"{/if}>
	<div class='row_color2'>
		<div class="leftColumn">
			{$name.friendly} expires separately from parent listing
		</div>
		<div class="rightColumn">
			<input type="checkbox" name="extras[{$name.internal}][use_durations]" value="1" {if $values.use_durations}checked="checked"{/if} onclick="ShowDurations_{$name.internal}((this.checked)?true:false);" />
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class='row_color1' id="{$name.internal}_flatfee" {if $values.use_durations}style="display: none;"{/if}>
		<div class="leftColumn">
			Price for full duration<br />
		</div>
		<div class="rightColumn">
			{$pre} <input type="text" name="extras[{$name.internal}][flat_fee]" value="{if $values.flat_fee}{$values.flat_fee}{else}0.00{/if}" /> {$post}
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class='row_color1' id="{$name.internal}_durations" {if !$values.use_durations}style="display: none;"{/if}>
		<div class="leftColumn">
			Select Durations and Pricing
		</div>
		<div class="rightColumn">
			<table border="1" cellpadding="2" id="{$name.internal}_durations_table">
				<thead>
					<th>Duration (in days)</th>
					<th>Price</th>
					{foreach $languages as $l}
						<th>Duration Label ({$l.name})</th>
					{/foreach}
					<th></th>
				</thead>
				{foreach $durations as $id => $d}
					<tr id="{$name.internal}_duration_{$id}" class="{cycle values='row_color1,row_color2'}">
						<td>{$d.days}</td>
						<td>{$d.price|displayPrice:$pre:$post}</td>
						{foreach $d.labels as $l}
							<td>{$l}</td>
						{/foreach}
						<td>
							<input type="button" value="Delete" onclick="DeleteDur_{$name.internal}({$id});" />
						</td>
					</tr>
				{/foreach}
				<tr id="{$name.internal}_new_duration">
					<td><input type="text" name="extras[{$name.internal}][new_duration][days]" placeholder="Ex: 30" /></td>
					<td>{$pre}<input type="text" name="extras[{$name.internal}][new_duration][price]" placeholder="Ex: 5.00" />{$post}</td>
					{foreach $languages as $l}
						<td><input type="text" name="extras[{$name.internal}][new_duration][labels][{$l.id}]" placeholder="Ex: Thirty Days (in {$l.name})" size="30" /></td>
					{/foreach}
					<td>
						<input type="submit" value="Add New" id="add_dur_{$name.internal}" />
					</td>
				</tr>
			</table>				
		</div>
		<div class="clearColumn"></div>
	</div>
</div>

<script type="text/javascript">
	ShowDurations_{$name.internal} = function(state) {
		if(state) {
			jQuery('#{$name.internal}_flatfee').hide();
			jQuery('#{$name.internal}_durations').show();
		} else {
			jQuery('#{$name.internal}_durations').hide();
			jQuery('#{$name.internal}_flatfee').show();
			
		}
	};
	DeleteDur_{$name.internal} = function(id) {
		jQuery('#main_pp_specifics_form').attr('action', jQuery('#main_pp_specifics_form').attr('action') + '&del=' + id + '#{$name.internal}_section');
		jQuery('#main_pp_specifics_form').submit();
	};
	jQuery('#add_dur_{$name.internal}').click(function() {
		//add anchor to form target
		jQuery('#main_pp_specifics_form').attr('action', jQuery('#main_pp_specifics_form').attr('action') + '#{$name.internal}_section'); 
	});
</script>