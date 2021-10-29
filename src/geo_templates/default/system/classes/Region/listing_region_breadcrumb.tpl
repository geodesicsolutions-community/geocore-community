{* 7.6.3-149-g881827a *}
<span class="listing_region_breadcrumb">
{foreach $regions as $level => $name}
<span class="region_level_{$level}">{$name}</span> 
{if !$name@last}<span class="region_level_divider">&gt;</span>{/if}
{/foreach}
</span>