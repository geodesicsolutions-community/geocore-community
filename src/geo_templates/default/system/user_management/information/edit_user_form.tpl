{* 16.09.0-49-g17f7099 *}
{if $errorAdminUser}
	<div class="field_error_box">For security reasons, the admin user's info may not be edited here.</div>
{else}
	<div class="content_box">
		<h1 class="title my_account">{$messages.636}</h1>
		<h3 class="subtitle">{$messages.514}</h3>
		<p class="page_instructions">{$messages.515}</p>
		
		<form action="{$formTarget}" autocomplete="off" method="post">
			<div class="{cycle values='row_odd,row_even'}">
				<label class="field_label">{$messages.516}</label>
				{$show->USERNAME}
			</div>
			{if $requirePass}
				<div class="{if $error.currentP}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="required">{$messages.500233} *</label>
					<input type="password" name="c[currentP]" class="field" />
					{if $error.currentP}<br /><span class="error_message">{$error.currentP}</span>{/if}
				</div>
			{/if}
		
			{if $canEditPassword}
				<div class="{if $error.password}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="field_label">{$messages.517}</label>
					<input type="password" name="c[password]" class="field" />
					{if $error.password}<br /><span class="error_message">{$error.password}</span>{/if}
				</div>
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">{$messages.518}</label>
					<input type="password" name="c[password_verify]" class="field" />
				</div>
			{elseif $demo}
				<div class="field_error_row">This user's password may not be edited in the demo.</div>
			{/if}
			
			<div class="{if $error.email}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
				<label class="required">{$messages.519} *</label>
				<input type="text" name="c[email]" class="field" value="{$info.email}" /> 
				{if $showCheckboxes}
					<input type="checkbox" name="c[apply_to_all_email]" value="1" />{$messages.500109}
					<input type="checkbox" name="d[expose_email]" value="1"{if $show->EXPOSE_EMAIL} checked="checked"{/if} />{$messages.1574}
				{/if}
				{if $error.email}<br /><span class="error_message">{$error.email}</span>{/if}
			</div>
				
			
			{if $rc->USE_REGISTRATION_COMPANY_NAME_FIELD}
				<div class="{if $error.company_name}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_COMPANY_NAME_FIELD}required{else}field_label{/if}">{$messages.520}{if $rc->REQUIRE_REGISTRATION_COMPANY_NAME_FIELD} *{/if}</label>
					<input type="text" name="c[company_name]" class="field" value="{$info.company_name|fromDB}" {if $rc->COMPANY_NAME_MAXLENGTH}maxlength="{$rc->COMPANY_NAME_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_company_name]" value="1"{if $show->EXPOSE_COMPANY_NAME} checked="checked"{/if} />{$messages.1574}{/if}
					{if $error.company_name}<br /><span class="error_message">{$error.company_name}</span>{/if}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_BUSINESS_TYPE_FIELD}
				<div class="{cycle values='row_odd,row_even'}">
					<label class="{if $rc->REQUIRE_REGISTRATION_BUSINESS_TYPE_FIELD}required{else}field_label{/if}">{$messages.521}{if $rc->REQUIRE_REGISTRATION_BUSINESS_TYPE_FIELD} *{/if}</label>
					<input type="radio" name="c[business_type]" value="1"{if $show->BUSINESS_TYPE == 1} checked="checked" {/if}/> {$messages.1572}
					<input type="radio" name="c[business_type]" value="2"{if $show->BUSINESS_TYPE == 2} checked="checked" {/if}/> {$messages.1571}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_FIRSTNAME_FIELD}
				<div class="{if $error.firstname}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_FIRSTNAME_FIELD}required{else}field_label{/if}">{$messages.522}{if $rc->REQUIRE_REGISTRATION_FIRSTNAME_FIELD} *{/if}</label>
					<input type="text" name="c[firstname]" class="field" value="{$info.firstname|fromDB}" {if $rc->FIRSTNAME_MAXLENGTH}maxlength="{$rc->FIRSTNAME_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_firstname]" value="1"{if $show->EXPOSE_FIRSTNAME} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.firstname}<br /><span class="error_message">{$error.firstname}</span>{/if}
				</div>
			{/if}
				
			{if $rc->USE_REGISTRATION_LASTNAME_FIELD}
				<div class="{if $error.lastname}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_LASTNAME_FIELD}required{else}field_label{/if}">{$messages.523}{if $rc->REQUIRE_REGISTRATION_LASTNAME_FIELD} *{/if}</label>
					<input type="text" name="c[lastname]" class="field" value="{$info.lastname|fromDB}" {if $rc->LASTNAME_MAXLENGTH}maxlength="{$rc->LASTNAME_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_lastname]" value="1"{if $show->EXPOSE_LASTNAME} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.lastname}<br /><span class="error_message">{$error.lastname}</span>{/if}
				</div>
			{/if}
		
			{if $rc->USE_REGISTRATION_ADDRESS_FIELD}
				<div class="{if $error.address}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_ADDRESS_FIELD}required{else}field_label{/if}">{$messages.524}{if $rc->REQUIRE_REGISTRATION_ADDRESS_FIELD} *{/if}</label>
					<input type="text" name="c[address]" class="field" value="{$info.address|fromDB}" {if $rc->ADDRESS_MAXLENGTH}maxlength="{$rc->ADDRESS_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_address]" value="1"{if $show->EXPOSE_ADDRESS} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.address}<br /><span class="error_message">{$error.address}</span>{/if}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_ADDRESS2_FIELD}
				<div class="{if $error.address_2}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_ADDRESS2_FIELD}required{else}field_label{/if}">{$messages.525}{if $rc->REQUIRE_REGISTRATION_ADDRESS2_FIELD} *{/if}</label>
					<input type="text" name="c[address_2]" class="field" value="{$info.address_2|fromDB}" {if $rc->ADDRESS_2_MAXLENGTH}maxlength="{$rc->ADDRESS_2_MAXLENGTH}"{/if} />
					{if $error.address_2}<br /><span class="error_message">{$error.address_2}</span>{/if}
				</div>
			{/if}
			
			{if $regionSelector}
				<div class="{if $error.location}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					{$regionSelector}
					{if $showCheckboxes}<input type="checkbox" name="d[expose_city]" value="1"{if $show->EXPOSE_CITY || $show->EXPOSE_COUNTRY || $show->EXPOSE_STATE} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.location}<br /><span class="error_message">{$error.location}</span>{/if}
				</div>
			{/if}
			
			{if $rc->USE_REGISTRATION_CITY_FIELD && !$regionOverrides.city}
				<div class="{if $error.city}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_CITY_FIELD}required{else}field_label{/if}">{$messages.526}{if $rc->REQUIRE_REGISTRATION_CITY_FIELD} *{/if}</label>
					<input type="text" name="c[city]" class="field" value="{$info.city|fromDB}" {if $rc->CITY_MAXLENGTH}maxlength="{$rc->CITY_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_city]" value="1"{if $show->EXPOSE_CITY} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.city}<br /><span class="error_message">{$error.city}</span>{/if}
				</div>
			{/if}
			
			{if $rc->USE_REGISTRATION_ZIP_FIELD}
				<div class="{if $error.zip}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_ZIP_FIELD}required{else}field_label{/if}">{$messages.528}{if $rc->REQUIRE_REGISTRATION_ZIP_FIELD} *{/if}</label>
					<input type="text" name="c[zip]" class="field" value="{$info.zip|fromDB}" {if $rc->ZIP_MAXLENGTH}maxlength="{$rc->ZIP_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_zip]" value="1"{if $show->EXPOSE_ZIP} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.zip}<br /><span class="error_message">{$error.zip}</span>{/if}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_PHONE_FIELD}
				<div class="{if $error.phone}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_PHONE_FIELD}required{else}field_label{/if}">{$messages.530}{if $rc->REQUIRE_REGISTRATION_PHONE_FIELD} *{/if}</label>
					<input type="text" name="c[phone]" class="field" value="{$info.phone|fromDB}" {if $rc->PHONE_MAXLENGTH}maxlength="{$rc->PHONE_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_phone]" value="1"{if $show->EXPOSE_PHONE} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.phone}<br /><span class="error_message">{$error.phone}</span>{/if}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_PHONE2_FIELD}
				<div class="{if $error.phone2}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_PHONE2_FIELD}required{else}field_label{/if}">{$messages.531}{if $rc->REQUIRE_REGISTRATION_PHONE2_FIELD} *{/if}</label>
					<input type="text" name="c[phone2]" class="field" value="{$info.phone2|fromDB}" {if $rc->PHONE_2_MAXLENGTH}maxlength="{$rc->PHONE_2_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_phone2]" value="1"{if $show->EXPOSE_PHONE2} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.phone2}<br /><span class="error_message">{$error.phone2}</span>{/if}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_FAX_FIELD}
				<div class="{if $error.fax}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_FAX_FIELD}required{else}field_label{/if}">{$messages.532}{if $rc->REQUIRE_REGISTRATION_FAX_FIELD} *{/if}</label>
					<input type="text" name="c[fax]" class="field" value="{$info.fax|fromDB}" {if $rc->FAX_MAXLENGTH}maxlength="{$rc->FAX_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_fax]" value="1"{if $show->EXPOSE_FAX} checked="checked"{/if} /> {$messages.1574}{/if}
					{if $error.fax}<br /><span class="error_message">{$error.fax}</span>{/if}
				</div>
			{/if}
			{if $rc->USE_REGISTRATION_URL_FIELD}
				<div class="{if $error.url}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $rc->REQUIRE_REGISTRATION_URL_FIELD}required{else}field_label{/if}">{$messages.533}{if $rc->REQUIRE_REGISTRATION_URL_FIELD} *{/if}</label>
					<input type="text" name="c[url]" class="field" value="{$info.url|fromDB}" {if $rc->URL_MAXLENGTH}maxlength="{$rc->URL_MAXLENGTH}"{/if} />
					{if $showCheckboxes}<input type="checkbox" name="d[expose_url]" value="1"{if $show->EXPOSE_URL} checked="checked"{/if} />{$messages.1574}{/if}
					{if $error.url}<br /><span class="error_message">{$error.url}</span>{/if}
				</div>
			{/if}
			{if count($orderItemFields) > 0}
				{foreach $orderItemFields as $f}
					<div class="{if $f.error}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
						{if $f.type == 'single_checkbox'}
							<input type="hidden" value="0" name="{$f.name}" />
							<input type="checkbox" value="1" name="{$f.name}" {if $f.checked}checked="checked"{/if} /> {$f.value}
						{else}
							<label class="{if $f.required}required{else}field_label{/if}">{$f.label}{if $f.required} *{/if}</label>
							{$f.value}
						{/if}
						{if $f.error}
							<br /><span class="error_message">{$f.error}</span>
						{/if}
					</div>
				{/foreach}
			{/if}
			{if count($addonFields) > 0}
				{foreach $addonFields as $addon => $addonField}
					{foreach $addonField as $f}
						<div class="{if $f.error}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
							{if $f.type == 'single_checkbox'}
								<input type="hidden" value="0" name="{$f.name}" />
								<label><input type="checkbox" value="1" name="{$f.name}" {if $f.checked}checked="checked"{/if} /> {$f.value}</label>
							{else}
								<label class="{if $f.required}required{else}field_label{/if}">{$f.label}{if $f.required} *{/if}</label>
								{$f.value}
							{/if}
							{if $f.error}
								<br /><span class="error_message">{$f.error}</span>
							{/if}
						</div>
					{/foreach}
				{/foreach}
			{/if}
			{foreach from=$optionals item=opt key=num}
				<div class="{if $opt.error}field_error_row{else}{cycle values='row_odd,row_even'}{/if}">
					<label class="{if $opt.required}required{else}field_label{/if}">{$opt.label}{if $opt.required} *{/if}</label>
					{if $opt.type == 'filter'}
						{$opt.info} <input type="hidden" name="c[optional_field_{$num}]" value="{$opt.info}" />
					{elseif $opt.type == 'text'}
						<input type="text" name="c[optional_field_{$num}]" value="{$opt.info}" size="30" {if $opt.maxlen}maxlength="{$opt.maxlen}"{/if} class="field" /> 
					{elseif $opt.type == 'area'}
						<textarea name="c[optional_field_{$num}]" rows="8" cols="30" class="field" />{$opt.info}</textarea>
					{elseif $opt.type == 'select'}
						<select name="c[optional_field_{$num}]" class="field">
							{foreach from=$opt.options item=drop}
								<option value="{$drop.value}"{if $drop.selected} selected="selected"{/if}>{$drop.value}</option>
							{/foreach}
						</select>
						{if $opt.useOther}
							{$messages.1265} <input type="text" name="c[optional_field_{$num}_other]"{if $opt.matched} value="{$opt.info}"{/if} size="15" {if $opt.maxlen}maxlength="{$opt.maxlen}"{/if} class="field"/>
						{/if}
					{/if}
					{if $showCheckboxes}<input type="checkbox" name="d[expose_optional_{$num}]" value="1"{if $opt.exposeChecked} checked="checked"{/if} />{$messages.1574}{/if}
					{if $opt.error}<br /><span class="error_message">{$opt.error}</span>{/if}
				</div>
			{/foreach}
			
			{if $feeshare_active == 1}
				{if $feeshare_attachment_type == 1}
					<div class="{cycle values='row_odd,row_even'}">
						<label class="field_label">{$feeshare_attachtouserlabel}</label>
						<textarea name="c[attached_user_message]" rows="8" cols="30" class="field" />{$feeshare_share_message}</textarea>
					</div>
				{elseif $feeshare_attachment_type == 2}
					<div class="{cycle values='row_odd,row_even'}">
						<label class="field_label">{$feeshare_attachtouserlabel}</label>
						<select name="c[user_attachment_id]" class="field"> 
							<option value="0"></option>
						{foreach from=$feeshare_userattachmentchoices item=name key=userid}
							<option value="{$userid}"{if $userid == $attachedtouser} selected="selected"{/if}>{$name}</option>
						{/foreach}
						</select>
						{if $error.feeshareattachmenterror}
							<span class="error_message">{$error.feeshareattachmenterror}</span>
						{/if}
					</div>			
				
				{/if}
			
			{/if}			
			
			{if $showCheckboxes}
				<div class="{cycle values='row_odd,row_even'}">
					<label class="field_label">{$messages.500126}</label>
					<input type="checkbox" name="c[apply_to_all_listings]" value="1" />
				</div>
				
				{if $using_mapping_fields}
					<div class="{cycle values='row_odd,row_even'}">
						<label class="field_label">{$messages.500127}</label>
						<input type="checkbox" name="c[apply_to_mapping]" value="1" />
					</div>
				{/if}
			{/if}
			
			<div class="center">
				<input type="submit" value="{$messages.534}" class="button" />
			</div>
		</form>
	</div>
	
	<div class="center">
		<a href="{$userManagementHomeLink}" class="button">{$messages.552}</a>
	</div>
{/if}