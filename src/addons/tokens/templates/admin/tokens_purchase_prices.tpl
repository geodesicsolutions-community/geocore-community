{* 6.0.7-3-gce41f93 *}
<div>
	<table>
		<tr class="col_hdr_top">
			<th>
				<strong>Number of Tokens</strong> 
			</th>
			<th>
				<strong>Price</strong> 
			</th>
			<th>
				<strong>Expiry Date</strong> 
			</th>
		</tr>
		{foreach $options as $option}
			<tr class="{cycle values='row_color1,row_color2'}">
				<td>
					<input type="text" name="tokens_purchase[options][{$option.tokens}][tokens]" value="{$option.tokens}" size="3" /> Tokens
				</td>
				<td>
					{$precurrency}<input type="text" name="tokens_purchase[options][{$option.tokens}][price]" value="{$option.price|displayPrice:'':''}" size="6" /> {$postcurrency}</td>
				<td>
					<input type="text" name="tokens_purchase[options][{$option.tokens}][expire_period]" value="{$option.expire_period}" size="3" />
					<select name="tokens_purchase[options][{$option.tokens}][expire_period_units]">
						<option value="{$day}"{if $option.expire_period_units==$day} selected="selected"{/if}>Days</option>
						<option value="{$year}"{if $option.expire_period_units==$year} selected="selected"{/if}>Years</option>
					</select>
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="3">
					<div class="page_note_error">No options configured yet!  Create a pricing option below.</div>
				</td>
			</tr>
		{/foreach}
		<tr class="col_ftr">
			<td><strong>Add New:</strong> <input type="text" name="tokens_purchase[options][new][tokens]" size="3" /> Tokens</td>
			<td>{$precurrency}<input type="text" name="tokens_purchase[options][new][price]" value="" size="6" /> {$postcurrency}</td>
			<td>
				<input type="text" name="tokens_purchase[options][new][expire_period]" value="5" size="3" />
				<select name="tokens_purchase[options][new][expire_period_units]">
					<option value="86400">Days</option>
					<option value="31536000" selected="selected">Years</option>
				</select>
			</td>
		</tr>
	</table>
</div>