{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}
{capture assign='editButton'}<div style="float: right; cursor: pointer;" class="text_blue">[Edit]</div>{/capture}
<fieldset>
	<legend>Currency Types Seller can accept from Buyer</legend>
	<div class="table-responsive">
		<form action="index.php?page=listing_currency_types_add" method="post">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr class="col_hdr_top">
						<th>Currency Name</th>
						<th>Pre-Currency</th>
						<th>Post-Currency</th>
						{if $is_auctions}
							<th>Conversion Rate</th>
						{/if}
						{if $sb_headers}
							{foreach $sb_headers as $label}
								<th>{$label}</th>
							{/foreach}
						{/if}
						<th>Display Order</th>
						<th></th>
				</thead>
				<tbody>
					{foreach $currencies as $currency}
						<tr class="{cycle values='row_color1,row_color2'}">
							<td>
								{$editButton}
								<input type="hidden" value="{$currency.type_name|escape}" />
								<div id="name__{$currency.type_id}" class="currency_edit">{$currency.type_name}</div>
							</td>
							<td class="center">
								{$editButton}
								<input type="hidden" value="{$currency.precurrency|escape}" />
								<div id="pre__{$currency.type_id}" class="currency_edit">{$currency.precurrency}</div>
							</td>
							<td class="center">
								{$editButton}
								<input type="hidden" value="{$currency.postcurrency|escape}" />
								<div id="post__{$currency.type_id}" class="currency_edit">{$currency.postcurrency}</div>
							</td>
							{if $is_auctions}
								<td class="center">
									{$editButton}
									<input type="hidden" value="{$currency.conversion_rate|escape}" />
									<div id="conversion__{$currency.type_id}" class="currency_edit">{$currency.conversion_rate}</div>
								</td>
							{/if}
							{if $sb_headers}
								{foreach $currency.sb_values as $sb_type => $sb_value}
									<td class="center">
										{$editButton}
										<select style="display: none;">
											{foreach $sb_currency_choices.$sb_type as $code => $sb_currency}
												<option value="{$code|escape}"{if $code==$sb_value} selected="selected"{/if}>{$sb_currency}</option>
											{/foreach}
										</select>
										<div id="{$currency.type_id}__{$sb_type}" class="sb_currency_edit">{$sb_currency_choices[$sb_type][$sb_value]}</div>
									</td>
								{/foreach}
							{/if}
							<td class="center">
								{$editButton}
								<input type="hidden" value="{$currency.display_order|escape}" />
								<div id="order__{$currency.type_id}" class="currency_edit">{$currency.display_order}</div>
							</td>
							<td class="center"><a href="index.php?page=listing_currency_types_delete&amp;type_id={$currency.type_id}&amp;auto_save=1" class="btn btn-danger btn-xs lightUpLink" style='margin:0;'><i class="fa fa-trash"></i> Delete</a>
						</tr>
					{foreachelse}
						<tr class="row_color1">
							<td colspan="7">
								<div class="page_note_error">No currencies found in the system!</div>
							</td>
						</tr>
					{/foreach}
					<tr class="col_ftr">
						<td>&nbsp;<strong>Add New:</strong> <input type="text" name="new[type_name]" /></td>
						<td class="center"><input type="text" name="new[precurrency]" size="8" /></td>
						<td class="center"><input type="text" name="new[postcurrency]" size="8" /></td>
						{if $is_auctions}
							<td class="center"><input type="text" name="new[conversion_rate]" value="1" size="5" /></td>
						{/if}
						{if $sb_headers}
							{foreach $sb_currency_choices as $sb_type => $sb_currencies}
								<td class="center">
									<select name="new[sb][{$sb_type}]">
										{foreach $sb_currencies as $code => $currency}
											<option value="{$code|escape}">{$currency}</option>
										{/foreach}
									</select>
								</td>
							{/foreach}
						{/if}
						<td class="center"><input type="text" name="new[display_order]" value="1" size="3" /></td>
						<td class="center"><input type="submit" class="mini_button" name="auto_save" value="Add New" /></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</fieldset>
<div class="page_note">
	<strong>Note:</strong> Currency symbols must be specified in their HTML Entity code format in order to be
	displayed properly.  Please reference the HTML Entity codes below for your desired currency symbol. There is no special HTML Entity code to enter for the dollar ($) symbol.
	<br /><br /><strong>Common Currency HTML Entity Codes:</strong>
	<ul>
		<li>&pound; British Pounds - HTML Entity: <strong>&amp;pound;</strong></li>
		<li>&euro; European Euro - HTML Entity: <strong>&amp;euro;</strong></li>
		<li>&yen; Japanese Yen - HTML Entity: <strong>&amp;yen;</strong></li>
	</ul>
</div>
