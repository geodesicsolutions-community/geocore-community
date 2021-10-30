{* 6.0.7-3-gce41f93 *}
<br />
<strong>{$title}</strong><br />
<ul style="margin-left: 0 auto; padding: 0; list-style-type: none;">
	{foreach from=$items item=item}
		<li class="{cycle values='row_color1,row_color2'}" style="padding: 5px; margin-left: 0px;">
			<img src="admin_images/design/arrowbullet.gif" alt="" /> 
			<span class="admin_rss_date">{$item.date|format_date:'D, M d, Y'}</span> - 
			<a href="{$item.link}" class="admin_rss_title" onclick="window.open(this.href); return false;">{$item.title}</a>
		</li>
	{/foreach}
</ul>