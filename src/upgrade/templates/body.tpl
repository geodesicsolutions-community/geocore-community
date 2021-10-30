{* 7.0.1-2-g29c6da3 *}

{if $upgradeIndex == 0}
	Click "Run Listed Upgrades" to proceed with the upgrade process. <br /><br />If any upgrade fails, 
	run the upgrade script again<br/> and it will pick up where it left off.
	<br /><br />
{elseif $upgradeStatus == -1}
	There was an error running one of the upgrades.  Usually it is safe to re-run the upgrade
	by clicking "Re-Start Upgrade".  Doing so will attempt to continue the upgrade process 
	starting at the step the error occured.  If there are any "Internal Error" messages above, 
	save them for reference in case they are needed later by Geodesic Support.
	<br /><br />
	If the upgrade continues to produce errors after re-running the upgrade, contact Geodesic Support.
	<br />
{elseif $moreUpdates}
	You have one or more upgrades to run.<br>Click the link below to run these upgrades.<br><br>
{elseif $cleanup}
	All main upgrades were run successfully! <br /><br />Click "Cleanup &amp; Finish" below to run the clean-up process and complete the upgrade wizard.<br>
{/if}
<br>
<table cellpadding="0" cellspacing="0" width="300" align="center">
	<thead>
		<tr>
			<th class="heading1" colspan="3">Upgrades to Run</th>
		</tr>
		<tr>
			<th class="heading2">Status</th>
			<th class="heading2">From</th>
			<th class="heading2a">To</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$upgrades item='value'}
			<tr>
				<td class="result">
					{if $value.status == -1}
						Error
					{elseif $value.status == 0}
						<span style='color: #EA1D25;'>Not Started</span>
					{elseif $value.status == 1}
						Currently Running
					{elseif $value.status == 2}
						<span style='color: #6B9133;'>Complete</span>
					{else}
						Unknown ({$value.status})
					{/if}
				</td>
				<td class="req">{$value.from}</td>
				<td class="setting">{$value.to}</td>
			</tr>
		{/foreach}
		<tr>
			<td class="result">
				{* Cleanup is never started when showing this page, when it is
				  finished, it will show the complete page.. *}
				<span style='color: #EA1D25;'>Not Started</span>
			</td>
			<td colspan="2" class="setting">
				<strong>Cleanup</strong>
			</td>
		</tr>
	</tbody>
</table><br />
{if $interHTML}
	<div class='interactiveBox'>
		{$interHTML}
	</div>
{/if}
{if $upgradeIndex == 0}
	<form action="index.php?run=show_upgrades" method="POST">
		<input type="hidden" name="license" value="on" />
		<input type="hidden" name="backup_agree" value="on" />
		<input type="hidden" name="licenseKey" value="{$licenseKey}" />
		<input type="submit" value="Run Listed Upgrades >>" />
	</form>
{elseif $upgradeStatus == '-1'}
	<form action="index.php?run=finish" method="POST">
		<input type="submit" value="Re-Start Upgrade" />
	</form> -or- 
	<form action="index.php?run=show_log&amp;next=continue" method="POST">
		<input type="submit" value="View Log" />
	</form>
{elseif $moreUpdates || $cleanup}
	<form action="index.php?run={if $cleanup}finish{else}show_upgrades{/if}" method="post">
		<input type="hidden" name="license" value="on" />
		<input type="hidden" name="backup_agree" value="on" />
		<input type="hidden" name="licenseKey" value="{$licenseKey}" />
		{if $cleanup}
			<input type="hidden" name="cleanup" value="1" />
			<input type="submit" value="Cleanup &amp; Finish >>" />
		{else}
			<input type="submit" value="Run Next Upgrade >>" />
		{/if}
	</form>
{/if}
