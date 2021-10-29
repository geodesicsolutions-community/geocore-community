{* 16.09.0-96-gf3bd8a1 *}
<div class='form-group'>
<label class='control-label col-md-5 col-sm-5 col-xs-12'>Starting Tokens Issued: </label>
  <div class='col-md-6 col-sm-6 col-xs-12 form-group'>
  	<input type="text" name="tokens[group_starting_tokens_count]" value="{$group_starting_tokens_count}" class='form-control col-md-7 col-xs-12' />
  </div>
</div>
<div class='form-group'>
<label class='control-label col-md-5 col-sm-5 col-xs-12'>Expire Starting Tokens: </label>
  <div class='col-md-6 col-sm-6 col-xs-12 input-group'>
	<input type="text" name="tokens[group_starting_tokens_expire_period]" value="{$group_starting_tokens_expire_period}" class='form-control col-md-7 col-xs-12 input-group-width40' />
	<select name="tokens[group_starting_tokens_expire_period_units]" class='form-control col-md-7 col-xs-12 input-group-width60'>
		<option value="{$day}"{if $group_starting_tokens_expire_period_units==$day} selected="selected"{/if}>Days</option>
		<option value="{$year}"{if $group_starting_tokens_expire_period_units==$year} selected="selected"{/if}>Years</option>
	</select>
	<div class='input-group-addon'>After Registration</div>
  </div>
</div>