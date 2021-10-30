{* 7.5.3-36-gea36ae7 *}

<div class="content_box">
	<h1 class="title">{$messages.1160}</h1>
	<h1 class="subtitle">{$messages.1161}</h1>
	<p class="page_instructions">{$messages.1162}</p>
	<form action="{$formTarget}" method="post">
		<input type="hidden" name="c[classified_id]" value="{$listing_id}" />
	
		{if $pageOptions.module_use_image}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[image]" class="field_label">{$messages.1314}</label>
					{if count($imageChoices) > 0}
						<select name="c[image]" id="c[image]"class="field">
							<option value="0">{$messages.1315}</option>
							{foreach from=$imageChoices key=k item=v}
								<option value="{$k}">{$v}</option>
							{/foreach}
						</select>
					{else}
						<strong>{$messages.1315}</strong><input type="hidden" name="c[image]" value="0" />
					{/if}
			</div>
		{/if}
		
		{if !$pageOptions.module_display_title} {* inverted logic: 0 = show title *}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[title]" class="field_label">{$messages.1163}</label>
				<input type="text" name="c[title]" id="c[title]" value="{$title}" class="field" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_price}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[price]" class="field_label">{$messages.1170}</label>
				<input type="text" name="c[price]" id="c[price]" value="{$price}" class="field" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_contact}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[contact]" class="field_label">{$messages.1164}</label>
				<input type="text" class="field" name="c[contact]" id="c[contact]" value="{$name}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_phone1}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[phone_1]" class="field_label">{$messages.1165}</label>
				<input type="text" class="field" name="c[phone_1]" id="c[phone_1]" value="{$phone}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_phone2}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[phone_2]" class="field_label">{$messages.1166}</label>
				<input type="text" class="field" name="c[phone_2]" id="c[phone_2]" value="{$phone2}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_address}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[address]" class="field_label">{$messages.1298}</label>
				<input type="text" class="field" name="c[address]" id="c[address]" value="{$address}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_city}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[city]" class="field_label">{$messages.1299}</label>
				<input type="text" class="field" name="c[city]" id="c[city]" value="{$city}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_state}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[state]" class="field_label">{$messages.1300}</label>
				<input type="text" class="field" name="c[state]" id="c[state]" value="{$state}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_zip}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[zip]" class="field_label">{$messages.1301}</label>
				<input type="text" class="field" name="c[zip]" id="c[zip]" value="{$zip}" />
			</div>
		{/if}
		
		{if $pageOptions.module_display_ad_description}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[description]" class="field_label">{$messages.1167}</label><br /><br />
				<textarea name="c[description]" id="c[description]" class="field" cols="" rows="" style="width: 97%; height: 200px;">{$description}</textarea>
			</div>
		{/if}
		
		{foreach from=$optionals item=opt key=i}
			<div class="{cycle values='row_odd,row_even'}">
				<label for="c[optional_field_{$i}]" class="field_label">{$opt.label}</label>
				<input type="text" class="field" name="c[optional_field_{$i}]" id="c[optional_field_{$i}]" value="{$opt.value}" />
			</div>
		{/foreach}

		<br />

		<div class="center">
			<input type="submit" name="submit" value="{$messages.1168}" class="button" />
		</div>
	</form>

</div>

<br />

<div class="center">
	<a href="{$backLink}" class="button">{$messages.1169}</a>
</div>
