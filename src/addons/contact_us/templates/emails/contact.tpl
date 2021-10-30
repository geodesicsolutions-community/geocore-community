{* 6.0.7-3-gce41f93 *}
{* Only use simple HTML as some e-mail clients are very limited *}

{if $show_ip}
	<strong>Sender IP:</strong>
	<span style="color: #006699;">{$ip}</span>
	<br /><br />
{/if}
<strong>Department: </strong>
<span style="color: #006699;">{$dept}</span>
<br /><br />
{if $username}
	<strong>Sender Username:</strong>
	<span style="color: #006699;">{$username|escape}</span>
	<br /><br />
{/if}
<strong>Sender's Name: </strong>
<span style="color: #006699;">{$name|escape}</span>
<br /><br />
<strong>Sender's E-Mail: </strong>
<span style="color: #006699;">{$email|escape}</span>
<br /><br />
<strong>Subject:</strong>
<span style="color: #006699;">{$subject|escape}</span>
<br /><br />
<strong>Message:</strong><br />
<div style="border : 1px solid #DDDDDD;
		padding: 10px;
		margin: 5px;
		background-color:#F5F5F5;
		color:#3C3C3C;
		font-family:Arial,Helvetica,sans-serif;">{$message|escape|nl2br}</div>
<br /><br />
<strong>End of Message</strong>
<br />