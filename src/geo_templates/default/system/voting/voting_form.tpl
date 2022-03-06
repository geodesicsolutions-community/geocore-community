{* 7.6.3-149-g881827a *}
{if !$noVoteReason}
	<form action="{$formTarget}" method="post">
{/if}

<div class="content_box">
	<h1 class="title">{$messages.1984}</h1>
	<p class="page_instructions">{$messages.1985}</p>
	
	{if $noVoteReason}
		<div class="success_box">{$noVoteReason}</div>
	{else}
		{if $error}
			<div class="field_error_box">{$messages.1986}</div>
		{/if}
		<h3 class="subtitle">{$title}</h3>
		<div class="row_odd">
			<label class="field_label">{$messages.1988}</label>
			<p style="white-space:nowrap;">
			<input type="radio" name="c[vote]" value="1" />&nbsp;<span class="glyphicon glyphicon-thumbs-up"></span>&nbsp;{$messages.1989}&nbsp;&nbsp;
			<input type="radio" name="c[vote]" value="2" />&nbsp;<span style="color: #BBB;" class="glyphicon glyphicon-thumbs-up"></span>&nbsp;{$messages.1990}&nbsp;&nbsp;
			<input type="radio" name="c[vote]" value="3" />&nbsp;<span style="color: #FF0000;" class="glyphicon glyphicon-thumbs-down"></span>&nbsp;{$messages.1991}
			</p>
		</div>
		<div class="row_even">
			<label for="c[vote_title]" class="field_label">{$messages.1993}</label>
			<input type="text" size="30" maxlength="30" name="c[vote_title]" id="c[vote_title]" class="field">
		</div>
		<div class="row_odd">
			<label for="c[vote_comments]" class="field_label">{$messages.1992}</label>
			<textarea name="c[vote_comments]" id="c[vote_comments]" cols="90" rows="6" class="field"></textarea>
		</div>
		<div class="center">
			<input type="submit" name="submit" value="{$messages.1994}" class="button" />
			<input type="reset" name="reset" class="button" value="{$messages.500889}" />
		</div>	
	{/if}
	<div class="center">
		<a href="{$backToCurrentAdLink}" class="button">{$messages.1996}</a>
	</div>
</div>

{if !$noVoteReason}
	</form>
{/if}
