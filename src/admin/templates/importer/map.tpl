{* 7.3.2-119-gc584f6b *}
{$adminMsgs}
<script>
//set up global field descriptions
var descs = {
	{foreach $fields as $groupNum => $group}
		{foreach $group as $saveName => $item}
			"{$saveName}":"{$item.description}"{if !($group@last && $item@last)},{/if}
			
		{/foreach}
	{/foreach}
};

var requirements = {

	{foreach $fields as $groupNum => $group}
		{foreach $group as $saveName => $item}
			{if $item.requires}
				"{$saveName}":"{$item.requires}"{if !($group@last && $item@last)},{/if}
			
			{/if}
		{/foreach}
	{/foreach}
};
</script>
<form action="" method="post">
	<div style='overflow: scroll;'>
	<fieldset>
		<legend>Field Mapping</legend>
		<div style="width: 100%;">
			<table style="width: 100%;">
				<tr>
					{foreach $demoTokens as $token}
						<td class="tokenCol">
							{$fieldNum = $token@index}
							
							<div class="fieldNum">
								Field Number: {$fieldNum}
								{if $csvHeaders}<br />
									{$csvHeaders.$fieldNum}
								{/if}
							</div>
							
							<select name="fieldselect[{$fieldNum}]" id="fieldselect_{$fieldNum}" class="fieldselect">
								{foreach $fields as $groupNum => $group}
									<optgroup label="{$fieldgroups.$groupNum}">
										{foreach $group as $saveName => $item}
											<option value="{$saveName}">{$item.name}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
							<br />
							
							<div class="description" id="desc_{$fieldNum}"></div>
							<script>
							//handle things that happen when user selects something in a dropdown
							jQuery('#fieldselect_{$fieldNum}').change(function () {
								//get chosen field
								var selected = jQuery('#fieldselect_{$fieldNum}').val();
								
								//make sure we haven't used this field elsewhere already (unless this is the "not used" field)
								if(selected != 'meta_not_used') {
									//find fieldselects that are not this one
									jQuery('.fieldselect:not(#fieldselect_{$fieldNum})').each(function (){
										if(jQuery(this).val() == selected) {
											//the selected value is already in use. show an alert and reset to not-used
											alert("The \""+selected+"\" field type is already in use. Make another selection");
											jQuery('#fieldselect_{$fieldNum}').val('meta_not_used');
											return false;
										}
									});
								}
								
								//check for active requirements for this field
								if(requirements[selected]) {
									//check through all fieldselects preceding this one
									var foundIt = false;
									jQuery('.fieldselect').slice(0,{$fieldNum}).each(function(f){
										if(jQuery(this).val() == requirements[selected]) {
											foundIt = true;
											return false; //done, so break out of .each() loop
										}
									});
									if(!foundIt) {
										//didn't find the requirement. show an alert and de-select
										alert("ERROR: The \""+requirements[selected]+"\" field MUST appear BEFORE the \""+selected+"\" field!");
										jQuery('#fieldselect_{$fieldNum}').val('meta_not_used');
										return false;
									}
								}
								
								//pull up the description for this field  
								jQuery('#desc_{$fieldNum}').text(descs[selected]);
							});
							jQuery('#fieldselect_{$fieldNum}').change();
							</script>
							
							<div class="token">
								Example value: {$token}
							</div>
							
							<div class="default">
								Default value: <input type="text" name="defaultval[{$fieldNum}]" />
							</div>
						</td>
					{/foreach}
				</tr>
			</table>
		</div>
	</fieldset>
	</div>
	<div class="center"><input type="submit" name="auto_save" value="Submit" /></div>
</form>