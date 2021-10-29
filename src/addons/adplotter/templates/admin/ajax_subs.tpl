{* 16.09.0-92-gefaf632 *}
{foreach $subs as $sub}
	<div class="form-group"> 
		<label class="control-label col-xs-12 col-sm-5">{$sub.name|fromDB}</label>
		<div class="col-xs-12 col-sm-6 vertical-form-fix">
			{$sub.ddl}
		</div>
	</div>
{/foreach}
