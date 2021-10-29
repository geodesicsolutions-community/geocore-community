{* 7.5.3-36-gea36ae7 *}
<table class='extra-questions'>
	{foreach $questions as $q}
		<tr>
			<td class='question'><strong>{$q.question}</strong></td>
			<td class='answer'>
				{if $q.link}<a href="{$q.link}"{if $open_window_user_links} target="_blank"{/if}{if $add_nofollow_user_links} rel="nofollow"{/if}>{/if}
				{$q.value}
				{if $q.link}</a>{/if}
			</td>
		</tr>
	{/foreach}
</table>