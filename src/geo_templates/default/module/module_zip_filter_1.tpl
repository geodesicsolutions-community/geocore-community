{* 6.0.7-3-gce41f93 *}
<label>{$messages.2305}</label>

<form action="" method="post">
	<div class="element">
		<input class="field" type="text" size="{$input_size}" maxlength="{$input_size}" value="{$local_zip_filter}" onfocus="if(this.value=='{$local_zip_filter}')this.value='';" name="set_zip_filter" />
		<select class="field" name="set_zip_filter_distance">
			<option value="">{$default_distance_text}</option>
			{foreach from=$opts item=o}
				<option {if $o.sel}selected="selected"{/if} value="{$o.distance}">{$o.distance}</option>
			{/foreach}
		</select>
		<input type="submit" name="submit_zip_filter" value="{$messages.2308}" class="button" />
		<input type="submit" name="clear_zip_filter" value="{$messages.2309}" class="button" />
	</div>
</form>