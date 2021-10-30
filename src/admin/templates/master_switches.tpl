{* 16.09.0-79-gb63e5d8 *}

<fieldset>
	<legend>Master Switches</legend>

	<div>
		{$admin_msgs}
		<div class="page_note">
			These are site-wide switches that control <strong>ENTIRE GROUPS OF FUNCTIONALITY</strong>. The intent is for you to turn on or off each setting
			depending upon your business model in order to more easily manage your site.<br />
			<br />
			Turning off functionality that you will not be using will show or hide certain parts on both the front end (such as some information on the My Account pages) and within the admin (such as certain settings), 
			but they will NOT affect text or templates that might mention the related feature without specifically being a part of it.<br />
			
		</div>
		<form action="index.php?page=master_switches" method="post" id="toggle_form">
			<input type="hidden" name="toggle" id="toggle_form_input" value="" />
			<input type="hidden" name="auto_save" value="1" />
		</form>
		<div class="master_switches_grid">
			{strip}
				{foreach $switches as $switch => $info}
					<div class="master_switch_{if $info.value=='on'}on{else}off{/if}" onclick="$('toggle_form_input').value='{$switch}'; $('toggle_form').submit();">
						<div class="master_switch_label">{$info.label}</div>
						<div class="master_switch_status">
							{if $info.value=='on'}
								<img src="admin_images/bullet_success.png" alt="on" /><br />
								On
							{else}
								<img src="admin_images/bullet_error.png" alt="off" /><br />
								Off				
							{/if}
						</div>
						<div class="clr"></div>
						<div class="master_switch_description">{$info.description}</div>
					</div>
				{/foreach}
			{/strip}
		</div>
	</div>
</fieldset>
<div class='clearColumn'></div>