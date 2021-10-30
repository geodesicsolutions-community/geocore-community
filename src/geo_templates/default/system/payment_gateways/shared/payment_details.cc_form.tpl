{* 17.10.0-4-g98652d0 *}

<span class="nowrap">
	<label for="cc_number" class="inline">{$messages.500295}</label>
	<input type="text" id="cc_number" name="c[cc_number]" size="20" class="field" autocomplete="off" />
</span>

{if $use_cvv2}
	<span class="nowrap">
		<label for="cvv2_code" class="inline"><a href="{external file='images/cvv2_code.gif'}" class="lightUpImg">{$messages.500296}</a></label>
		<input type="text" id="cvv2_code" name="c[cvv2_code]" size="4" class="field" autocomplete="off" />
	</span>
{/if}

<span class="nowrap">
	<label class="inline">{$messages.500297}</label>
	{html_select_date end_year="+19" display_days=0 field_array="c[exp_date]"}
</span>

{if $error_msgs.cc_result_message}
	<div class="error_message">{$error_msgs.cc_result_message}</div>
{/if}
{if $error_msgs.cc_number}
	<div class="error_message">{$error_msgs.cc_number}</div>
{/if}
{if $error_msgs.cvv2_code}
	<div class="error_message">{$error_msgs.cvv2_code}</div>
{/if}
{if $error_msgs.exp_date}
	<div class="error_message">{$error_msgs.exp_date}</div>
{/if}
