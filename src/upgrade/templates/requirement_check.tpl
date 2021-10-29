{* 16.03.0-3-gb5f334d *}
<form action="index.php?run=show_upgrades" method="POST" id="req_form">
<div style="border: 2px solid #1382B7; padding: 3px; background-color:#FFF;"> 
<table cellpadding="2" cellspacing="2">
	<thead>
		<tr>
			<th class="heading1" colspan="3">Server Minimum Requirements Check</th>
		</tr>
		<tr>
			<th width="12%" class="heading2">Req&nbsp;Met?</th>
			<th width="30%" class="heading2">Requirement</th>
			<th class="heading2a">Your Server's Settings</th>
		</tr>
	</thead>
	<tbody>
		<tr style="background-color: #FFF;">
			<td class="result">{$php_version_result}</td>
			<td class="req">{$php_version_req}</td>
			<td class="setting">{$php_version_text}</td>
		</tr>
		<tr style="background-color: #FFF;">
			<td class="result">{$mysql_result}</td>
			<td class="req">{$mysql_req}</td>
			<td class="setting">{$mysql_text}</td>
		</tr>
		<tr style="background-color: #FFF;">
			<td class="result">{$ioncube_ini_result}</td>
			<td class="req">{$ioncube_ini_req}</td>
			<td class="setting">{$ioncube_ini_text}</td>
		</tr>
	</tbody>
</table>
</div>
<br />
{$overall_result}
{$continue}
</form>
<form action="index.php" method="GET">
{$developer_force_version}
</form>