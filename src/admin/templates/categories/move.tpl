{* 7.3.3-139-g0040985 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Move Categor{$cat_plural}</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=category_move&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This can take some time if you are moving a lot of categories,
		or are moving categories that have a lot of sub-categories.  Select where to move the categories to below:
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">FROM Parent Category</div>
		<div class="rightColumn">
			{if $parents}
				Top Level &gt;
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
		<div class="leftColumn">Move TO Sub-categories Of</div>
		<div class="rightColumn">
			{if $parent}<label><input type="radio" name="to_type" value="top" checked="checked" class="move_to_type" /> Top Level</label><br />{/if}
			<label><input type="radio" name="to_type" value="id" class="move_to_type" {if !$parent} checked="checked"{/if}/> Enter Category ID#</label><br />
			<div id="moveToIdBox" style="margin-left: 20px;{if $parent} display: none;{/if}">				
				<input type="text" name="new_parent" size="3" title="ID#" />
			</div>
			
			<label><input type="radio" name="to_type" value="browse" class="move_to_type" /> Select Category</label>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div id="moveToBrowseBox" style="display: none;">
		{include file='categories/moveBrowse.tpl'}
	</div>
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Move" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
</form>