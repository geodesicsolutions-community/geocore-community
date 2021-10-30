{* 7.3.1-98-g4710736 *}

<div class="closeBoxX"></div>

<div class="lightUpTitle" style="min-width: 400px;">Category Specific Template Attachments for {$categoryName}</div>
<p>
	<strong>Category: </strong> {$categoryName} (ID# {$categoryId})<br />
	<strong>Showing Attachments From:</strong> Active template sets<br />
</p>
<div  style="height: 400px; overflow: auto;">
<table>
	<thead>
		<tr class="col_hdr">
			<th>Page Name</th>
			{foreach from=$languages item=lang key=lang_id}
				<th>
					{if $lang_id==1}
						Base/Fallback Language
					{else}
						{$lang} (#{$lang_id}) Language
					{/if}
				</th>
			{/foreach}
			<th></th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$pages item=page key=page_id}
			<tr class="{cycle values="row_color1,row_color2"}">
				<td>
					{$page.name}
				</td>
				{foreach from=$languages item=lang key=lang_id}
					<td>
						{if $page.attachments[$lang_id][$categoryId]}
							<span title="{$page.attachments[$lang_id][$categoryId]|escape}">
								{$page.attachments[$lang_id][$categoryId]|truncate:30:'...'}
							</span>
						{else}
							{if !$page.attachments.1.0}
								<strong style="color: red;">None Attached!</strong>
							{else}
								{if $lang_id!=1 && $page.attachments[$lang_id][0]}
									<span title="{$page.attachments[$lang_id][0]|escape}">
										{$page.attachments[$lang_id][0]|truncate:30:'...'}
									</span>
									<p class="small_font">
										<strong>Inherited From:</strong><br />
										All Categories<br />
										{$lang} ({$lang_id}) Language
									</p>
								{elseif $page.attachments[1][$categoryId]}
									<span title="{$page.attachments[1][$categoryId]|escape}">
										{$page.attachments[1][$categoryId]|truncate:30:'...'}
									</span>
									<p class="small_font">
										<strong>Inherited From:</strong><br />
										{$categoryName} ({$categoryId}) Category<br />
										Base/Fallback Language
									</p>
								{else}
									<span title="{$page.attachments.1.0|escape}">
										{$page.attachments.1.0|truncate:30:'...'}
									</span>
									<p class="small_font">
										<strong>Inherited From:</strong><br />
										Default (Site-Wide) Template
									</p>
								{/if}
							{/if}
						{/if}
					</td>
				{/foreach}
				<td>
					<a href="index.php?page=page_attachments_edit&amp;pageId={$page_id|escape}" class="mini_button">Edit Attachments</a>
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
</div>
<div style="float: right;">
	<br />
	<a href="#" class="closeLightUpBox mini_button">Close</a>
</div>
