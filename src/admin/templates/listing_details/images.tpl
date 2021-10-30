{* 6.0.7-3-gce41f93 *}

<div class="col_hdr_top">Listing Photos &amp; Files</div>
<br /><br />
<table cellpadding="2" cellspacing="1" border="0" align="center" width="100%">
	{foreach from=$images item='image'}
		<tr>
			<td align="center" valign="top">
				{if $image.icon}
					<a href="{if !$image.is_abs_url}../{/if}{$image.image_url}">
						<img src="{$image.icon}" alt="{$image.image_text|escape}" />
					</a>
				{else}
					{if $image.thumb_url && $image.image_url!=$image.thumb_url}
						<a href="{if !$image.is_abs_url}../{/if}{$image.image_url}" class="lightUpImg">
							<img src="{if !$image.is_abs_url}../{/if}{$image.thumb_url}" alt="" />
						</a>
					{else}
						<img src="{if !$image.is_abs_url}../{/if}{$image.image_url}" alt="" />
					{/if}
				{/if}
				<br />
				{$image.image_text}
			</td>
		</tr>
	{/foreach}
</table>
