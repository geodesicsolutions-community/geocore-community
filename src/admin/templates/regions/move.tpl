{* 7.1beta1-1113-gb4133d1 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Move Region(s)</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=region_move&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $regions as $region_id}
		<input type="hidden" name="regions[]" value="{$region_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This can take some time if you are moving a lot of regions,
		or are moving regions that have a lot of sub-regions.  Select where to move the regions to below:
	</p>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Current Location</div>
		<div class="rightColumn">
			{if $parents}
				Top Level >
				{foreach $parents as $p}
					{$p.name}
					{if !$p@last} &gt;{/if}
				{/foreach}
			{else}
				Top Level
			{/if}
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Move To Subregions Of</div>
		<div class="rightColumn">
			{if $parent}<label><input type="radio" name="to_type" value="top" checked="checked" class="move_to_type" /> Top Level</label><br />{/if}
			<label><input type="radio" name="to_type" value="id" class="move_to_type" {if !$parent} checked="checked"{/if}/> Enter Region ID# / Unique Name</label><br />
			<div id="moveToIdBox" style="margin-left: 20px;{if $parent} display: none;{/if}">				
				<input type="text" name="new_parent" size="3" title="ID# or Unique Name" />
			</div>
			
			<label><input type="radio" name="to_type" value="browse" class="move_to_type" /> Select Region</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div id="moveToBrowseBox" style="display: none;">
		{include file='regions/moveBrowse.tpl'}
	</div>
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Move" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>