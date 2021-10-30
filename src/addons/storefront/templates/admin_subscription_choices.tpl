{* 16.09.0-106-ge989d1f *}
{$messages}
<fieldset>
<legend>Storefront Subscription Choices</legend>
	<form action='index.php?page=storefront_subscription_choices_add' method='post' class='form-horizontal'>
		<div class='table-responsive'>
			<table class='table table-striped table-hover table-bordered'>
				<thead>
					<tr>
						<th style="text-align: center;">Period duration name </th>
						<th style="text-align: center;">Period </th>
						<th style="text-align: center;">Cost </th>
						<th style="text-align: center;">Trial Period</th>
						<th>&nbsp; </th>
						<th>&nbsp; </th>
					</tr>
				</thead>
			{foreach from=$count_display item=id}
				{$choice_id=$period_ids.$id}
				<tr>
					<td class="medium_font center">
						{$display_values.$id}
					</td>
					<td class="medium_font center">
						{$numberofdays.$id} day{$value_plural.$id}
					</td>
					<td class="medium_font center">
						{$amount.$id|displayPrice}
					</td>
					<td class="medium_font center">
						{if $trial.$id}Yes{else}No{/if}
					</td>
					<td class="medium_font center">		
						<a href="index.php?page=storefront_subscription_choices_edit&amp;period_id={$choice_id}" class="btn btn-xs btn-info"><i class="fa fa-pencil"></i> Edit</a>
					</td>
					<td class="medium_font center">
						<a href="index.php?page=storefront_subscription_choices_delete&amp;period_id={$choice_id}&amp;auto_save=1" class="lightUpLink btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> Delete</a>
					</td>
				</tr>
			{foreachelse}
				<tr><td colspan="6"><div class='page_note_error'>No Subscription Choices</div></td></tr>
			{/foreach}
				
				<tr class='col_ftr'>
					<td>
						<div class="form-group">
							<input type=text name=d[display_value] value='30 Days' class='form-control' />
						</div>
					</td>
					<td>
						<div class="form-group">
							<div class='input-group'>
								<input type=text name='d[value]' value='30' class='form-control' />
								<div class='input-group-addon'>days</div>
							</div>
						</div>
					</td>
					<td>
						<div class="form-group">
							<div class='input-group'>
								<div class='input-group-addon'>{$precurrency}</div>
								<input type=text name='d[cost]' value='5.00' class='form-control' />
								<div class='input-group-addon'>{$postcurrency}</div>
							</div>
						</div>
					</td>
					<td>
						<div class="center" style="padding-top: 8px;"><input type="checkbox" name="d[trial]" value="1" /></div>
					</td>
					<td colspan="2">
						<div class="center"><input type='submit' class='btn btn-xs btn-success' name='auto_save' value='Add Choice' /></div>
					</td>
					
					
				</tr>
				
			</table>
		</div>
	</form>
</fieldset>
{if $plans}
<fieldset>
	<legend>Price Plan Specific Usage</legend>
	<div class=page_note>
		<p>Each price plan can have which ever subscription choices available to it,
			or even have the Storefront disabled totally. By default
			none of the subscription choices listed above are enabled for any
			price plans. To enable them for a price plan, click the appropriate 
			link below to see settings for that price plan, then
			click <strong>configure</strong> next to the option for <strong>Storefront Subscription</strong>.
			<br /><br />See the User Manual for more information.</p>
		{$plans}
	</div>
</fieldset>
{/if}