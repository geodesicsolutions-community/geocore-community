{* 7.3.3-139-g0040985 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Copy Categories</div>

<form style="display:block; margin: 15px; width: 450px;" action="index.php?page=category_copy&amp;parent={$parent}&amp;p={$page}" method="post">
	{foreach $values as $value_id}
		<input type="hidden" name="values[]" value="{$value_id}" />
	{/foreach}
	<p class="page_note">
		<strong>Warning:</strong> This can take some time if you are copying a lot of categories,
		or are copying categories that have a lot of sub-categories.  Select where to copy the categories to below:
	</p>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">FROM Parent Value</div>
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
		<div class="leftColumn">Copy TO Sub-categories Of</div>
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
		{include file='categories/copyBrowse.tpl'}
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="copy_questions" value="1" checked="checked" /></div>
		<div class="rightColumn">
			Copy Category-Specific <strong>Questions</strong>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="copy_fields" value="1" /></div>
		<div class="rightColumn">
			Copy Category-Specific <strong>Fields to Use</strong>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="copy_price_plans" value="1" /></div>
		<div class="rightColumn">
			Copy Category-Specific <strong>Price Plans</strong>
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="copy_subcategories" value="1" checked="checked" /></div>
		<div class="rightColumn">
			Copy ALL Sub-Categories Recursively
		</div>
		<div class="clearColumn"></div>
	</div>
	
	<br /><br />
	<div style="float: right;">
		<input type="submit" name="auto_save" value="Copy" class="mini_button" />
		<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
	</div>
	<br />
</form>