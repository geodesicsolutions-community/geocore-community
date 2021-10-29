{* 7.2.4-16-gb73e84f *}

{* NOTE: This is used by the main display, and by ajax calls. to update the combined
	step sections. *}
<div class="combined_loading_overlay" style="display: none;">
	<img src="{if $in_admin}../{/if}{external file='images/loading.gif'}" alt="..." />
</div>
{* Use body_html tag to show each step on same page *}
{$geo_inc_files=$step_info.geo_inc_files}
{$body_vars=$step_info.body_vars}
{body_html _sub_body_html=true}