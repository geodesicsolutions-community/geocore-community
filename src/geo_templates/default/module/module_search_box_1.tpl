{* 7.0.2-68-g1edd2b8 *}

<div id="search_box_1">
	<form action="{$form_target}" method="get">
		<ul>
			<li>
				<input type="hidden" name="a" value="19" />
				<input type="text" name="b[search_text]" class="field keyword" {if $placeholder} placeholder="{$placeholder|escape}"{/if} />
			</li>
			<li>{$category_dropdown}</li>
			{if !$hidden_fields}
				<li>
					<select name="b[search_by_field]" class="field">
						<option value="all_fields">{$messages.1867}</option>	
						<option value="title_only">{$messages.1868}</option>	
						<option value="description_only">{$messages.1869}</option>
						{if $opt1}<option value="optional_field_1">{$messages.1870}</option>{/if}
						{if $opt2}<option value="optional_field_2">{$messages.1871}</option>{/if}
						{if $opt3}<option value="optional_field_3">{$messages.1872}</option>{/if}
						{if $opt4}<option value="optional_field_4">{$messages.1873}</option>{/if}
						{if $opt5}<option value="optional_field_5">{$messages.1874}</option>{/if}
						
						{if $opt6}<option value="optional_field_6">{$messages.1875}</option>{/if}
						{if $opt7}<option value="optional_field_7">{$messages.1876}</option>{/if}
						{if $opt8}<option value="optional_field_8">{$messages.1877}</option>{/if}
						{if $opt9}<option value="optional_field_9">{$messages.1878}</option>{/if}
						{if $opt10}<option value="optional_field_10">{$messages.1879}</option>{/if}
						
						{if $opt11}<option value="optional_field_11">{$messages.1880}</option>{/if}
						{if $opt12}<option value="optional_field_12">{$messages.1881}</option>{/if}
						{if $opt13}<option value="optional_field_13">{$messages.1882}</option>{/if}
						{if $opt14}<option value="optional_field_14">{$messages.1883}</option>{/if}
						{if $opt15}<option value="optional_field_15">{$messages.1884}</option>{/if}
						
						{if $opt16}<option value="optional_field_16">{$messages.1885}</option>{/if}
						{if $opt17}<option value="optional_field_17">{$messages.1886}</option>{/if}
						{if $opt18}<option value="optional_field_18">{$messages.1887}</option>{/if}
						{if $opt19}<option value="optional_field_19">{$messages.1888}</option>{/if}
						{if $opt20}<option value="optional_field_20">{$messages.1889}</option>{/if}
					</select>
				</li>
			{/if}
			{if $addonExtra}
				{foreach from=$addonExtra item=addonContents}
					<li>{$addonContents}</li>
				{/foreach}
			{/if}
			<li>
				{if $hidden_fields}
					<input type="hidden" name="b[search_descriptions]" value="1" />
					<input type="hidden" name="b[search_titles]" value="1" />
				{/if}
				<input type="hidden" name="b[subcategories_also]" value="1" />
				<input type="submit" value="{$messages.1627}" class="button" />
			</li>
			
			{if $zipsearchByLocation_html}
				<li>{$zipsearchByLocation_html}</li>
			{/if}
		</ul>
	</form>
</div>