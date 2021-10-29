{* 7.5.3-36-gea36ae7 *}
<ul class='extraQuestionValue' id='extraQuestionValue'>
	{foreach $answers as $a}
		<li>
			{if $a.link}<a href="{$a.link}"{if $open_window_user_links} target="_blank"{/if}{if $add_nofollow_user_links} rel="nofollow"{/if}>{/if}
			{$a.value}
			{if $a.link}</a>{/if}
		</li>
	{/foreach}
</ul>