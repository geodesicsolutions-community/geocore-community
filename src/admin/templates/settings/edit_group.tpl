{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

<div class="page-title1">User Group: <span class="group-color">{$group.name}</span></div>
<form action="index.php?page=users_group_edit&amp;c={$group_id}" method="post" class="form-horizontal form-label-left">
	<fieldset>
		<legend>User Group Details</legend>
		<div class="x_content">
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Group ID#: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'><span class="vertical-form-fix">{$group_id}</span>
			  </div>
			</div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Group Name: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  	<input type="text" name="d[name]" class="form-control col-md-7 col-xs-12" value="{$group.name|escape}" />
			  </div>
			</div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Group Description: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  	<textarea name="d[description]" class="form-control">{$group.description|escape}</textarea>
			  </div>
			</div>
			{if $is_ent||$is_premier}
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Registration Code: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				  	<input type="text" name="d[registration_code]" class="form-control col-md-7 col-xs-12" value="{$group.registration_code|escape}" />
				  </div>
				</div>
			{else}
				<input type="hidden" name="d[registration_code]" class="form-control col-md-7 col-xs-12" value="" />
			{/if}
			{if $is_ent}
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Questions Attached to this Group: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				 		<span class="vertical-form-fix">
						{foreach $questions as $question}
							{$question.name}<br />
						{foreachelse}
							None
						{/foreach}
						<br />
						<a href="index.php?mc=users&amp;page=users_group_questions&amp;d={$group_id}" class="mini_button">Edit / Add Group Questions</a>
						</span>
				  </div>
				</div>

			{/if}
			{$addonSettings}
			<div style="text-align:right;">
				<input type="submit" name="auto_save" value="Quick Save" class="mini_button" />
			</div>
		</div>
	</fieldset>
	
{*
TODO:  finish templatizing the entire page..  Only did this section for now since just
trying to quickly add addon hook to allow showing settings.  
Closing </form> tag added in PHP file. 
*}