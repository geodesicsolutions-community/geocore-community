{* 7.5.3-125-gf5f0a9a *}
{if $is_ajax}
	{* loading this as the results of an ajax pagination call. only interested in the internal data, which will replace the contents of div#module_content_{tag} below *}
	{include file=$browse_tpl g_type='system' g_resource='browsing'}
	{if $module_pagination}<div>{$module_pagination}</div>{/if}
{else}
	{* this is the "main" display (first page) *}
	{if $module.module_display_header_row && $header_title}
		<h3 class="title">{$header_title}</h3>
	{/if}
	<div id="module_content_{$module.module_replace_tag}">
		{include file=$browse_tpl g_type='system' g_resource='browsing'}
		{if $module_pagination}<div>{$module_pagination}</div>{/if}
	</div>
	{if $module_pagination}
		<script>
			LoadModulePage_{$module.module_replace_tag} = function(page) {
				jQuery.post('AJAX.php?controller=ModuleControls&action=GetPage',
					{
						tag: '{$module.module_replace_tag}',
						results_page: page,
						params: {$params_json} {*this way any smarty-set parameters get passed along to the next "page" *} 
					},
					function(returned) {
						jQuery('#module_content_{$module.module_replace_tag}').html(returned);
						//add lightbox observers to new images (which wouldn't have been there when the page was initially created)
						gjUtil.lightbox.initClick();
					},
					'html'
				);
			};
		</script>
	{/if}
{/if}