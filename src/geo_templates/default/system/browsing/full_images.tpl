{* 7.5.3-125-gf5f0a9a *}

<div class="sidebar-collapse">
	<div class="content_box">
		<h1 class="title">{$messages.500810}</h1>
		<ul class="option_list">
			<li><a href="{$classifieds_file_name}?a=20&amp;b={$id}">{$messages.1358}</a></li>
			<li><a href="{$classifieds_file_name}?a=12&amp;b={$id}">{$messages.1359}</a></li>
			{if not $anonymous}
				<li><a href="{$classifieds_file_name}?a=13&amp;b={$id}">{$messages.1360}</a></li>
				<li><a href="{$classifieds_file_name}?a=6&amp;b={$seller}">{$messages.1361}</a></li>
			{/if}
		</ul>
		<br />
		<div class="center">
			<a href="{if $aff_url}{$aff_url}&amp;{else}{$classifieds_file_name}?{/if}a=2&amp;b={$id}" class="button">{$messages.1357}</a>
		</div>
	</div>
</div>

<div id="content_column_wide">
	<div class="content_box clearfix">
		<h3 class="title">{$title}</h3>
		
		<p class="page_instructions">{$description}</p>
		<div class="box_pad">
			{foreach from=$images item=image}
				<div class="full_image_item">
					{$image}
				</div>
			{/foreach}
		</div>
	</div>
</div>
