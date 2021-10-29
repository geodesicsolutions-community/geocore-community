{* 16.09.0-79-gb63e5d8 *}
<fieldset>
	<legend>Template Attachment(s)</legend>
	<div>
		<p class="page_note">
			<strong style="font-size: 1.2em;">Showing Attachments From: <span class="color-primary-one">Active Template Sets</span></strong><br />
			The template attachments shown below are the templates that will be used to display the page.
			If you have multiple template sets on your site, it will use the first template attachments
			it finds, starting from the template set at the "top of the active list".
		</p>
		<div class="table-responsive">
		<table class="table table-hover table-striped table-bordered">
			<thead>
				<tr class="col_hdr_top">
					{if count($pages)>1 || $extraPage}
						<th>Attachment For</th>
					{/if}
					{foreach from=$languages item=lang key=lang_id}
						<th>
							{if $lang_id==1}
								<span class="color-primary-one">Base/Fallback</span> Language
							{else}
								<span class="color-primary-one" style="text-transform: uppercase;">{$lang} (#{$lang_id})</span> Language
							{/if}
						</th>
					{/foreach}
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$pages item=page key=page_id}
					<tr class="{cycle values="row_color1,row_color2"}">
						{if count($pages)>1 || $extraPage}
							<td>
								{$page.name}
							</td>
						{/if}
						{foreach from=$languages item=lang key=lang_id}
							<td>
								{if $page.attachments[$lang_id][$categoryId]}
									<span title="{$page.attachments[$lang_id][$categoryId]|escape}">
										{$page.attachments[$lang_id][$categoryId]|truncate:30:'...'}
									</span>
									<p class="small_font">
										<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span>
									</p>
								{else}
									{if !$page.attachments.1.0}
										<strong style="color: red;">None Attached!</strong>
									{else}
										{if $lang_id!=1 && $page.attachments[$lang_id][0]}
											<span title="{$page.attachments[$lang_id][0]|escape}">
												{$page.attachments[$lang_id][0]|truncate:30:'...'}
											</span>
											<p class="small_font">
												<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span><br />
												<strong>Inherited From:</strong><br />
												{$lang} ({$lang_id}) Language
											</p>
										{elseif $page.attachments[1][$categoryId]}
											<span title="{$page.attachments[1][$categoryId]|escape}">
												{$page.attachments[1][$categoryId]|truncate:30:'...'}
											</span>
											<p class="small_font">
												<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span><br />
												<strong>Inherited From:</strong><br />
												Base/Fallback Language
											</p>
										{else}
											<span title="{$page.attachments.1.0|escape}">
												{$page.attachments.1.0|truncate:30:'...'}
											</span>
											<p class="small_font">
												<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span><br />
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
					{if  $extraPage}
						<tr class="{cycle values="row_color1,row_color2"}">
							<td>
								Extra Page Contents {ldelim}body_html} Sub-Template
							</td>
							{foreach from=$languages item=lang key=lang_id}
								<td>
									{if $page.attachments.extra_page_main_body[$lang_id][$categoryId]}
										<span title="{$page.attachments.extra_page_main_body[$lang_id][$categoryId]|escape}">
											{$page.attachments.extra_page_main_body[$lang_id][$categoryId]|truncate:30:'...'}
										</span>
										<p class="small_font">
											<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span>
										</p>
									{else}
										{if !$page.attachments.extra_page_main_body.1.0}
											<strong style="color: red;">None Attached!</strong>
										{else}
											{if $lang_id!=1 && $page.attachments.extra_page_main_body[$lang_id][0]}
												<span title="{$page.attachments.extra_page_main_body[$lang_id][0]|escape}">
													{$page.attachments.extra_page_main_body[$lang_id][0]|truncate:30:'...'}
												</span>
												<p class="small_font">
													<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span><br />
													<strong>Inherited From:</strong><br />
													{$lang} ({$lang_id}) Language
												</p>
											{elseif $page.attachments.extra_page_main_body[1][$categoryId]}
												<span title="{$page.attachments.extra_page_main_body[1][$categoryId]|escape}">
													{$page.attachments.extra_page_main_body[1][$categoryId]|truncate:30:'...'}
												</span>
												<p class="small_font">
													<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span><br />
													<strong>Inherited From:</strong><br />
													Base/Fallback Language
												</p>
											{else}
												<span title="{$page.attachments.extra_page_main_body.1.0|escape}">
													{$page.attachments.extra_page_main_body.1.0|truncate:30:'...'}
												</span>
												<p class="small_font">
													<strong>Attachment Set in:</strong> <span class="text_green">{$page.t_set}</span><br />
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
					{/if}
				{/foreach}
			</tbody>
		</table>
		</div>
	</div>
</fieldset>
