{* 4eb7314 *}


<div class="pedigree_tree">
	<ul class="pedigree_gen1">
		<li class="sire">
			<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.name|escape:url}">
				{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.sire.name|capitalize}
				{if $show_label}
					<br /><span>{$msgs.sire}</span>
				{/if}
			</a>
		</li>
		<li class="dam">
			<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.name|escape:url}">
				{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.dam.name|capitalize}
				{if $show_label}
					<br /><span>{$msgs.dam}</span>
				{/if}
			</a>
		</li>
	</ul>
	{if $maxGen>1}
		<ul class="pedigree_gen2">
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.sire.name|escape:url}">
					{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.sire.sire.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.sires} {$msgs.sire}</span>
					{/if}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.dam.name|escape:url}">
					{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.sire.dam.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.sires} {$msgs.dam}</span>
					{/if}
				</a>
			</li>
			<li class="sire dam_sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.sire.name|escape:url}">
					{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.dam.sire.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.dams} {$msgs.sire}</span>
					{/if}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.dam.name|escape:url}">
					{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.dam.dam.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.dams} {$msgs.dam}</span>
					{/if}
				</a>
			</li>
		</ul>
	{/if}
	{if $maxGen>2}
		<ul class="pedigree_gen3">
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.sire.sire.name|escape:url}">
					{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.sire.sire.sire.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.sires} {$msgs.sires} {$msgs.sire}</span>
					{/if}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.sire.dam.name|escape:url}">
					{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.sire.sire.dam.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.sires} {$msgs.sires} {$msgs.dam}</span>
					{/if}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.dam.sire.name|escape:url}">
					{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.sire.dam.sire.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.sires} {$msgs.dams} {$msgs.sire}</span>
					{/if}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.dam.dam.name|escape:url}">
					{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.sire.dam.dam.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.sires} {$msgs.dams} {$msgs.dam}</span>
					{/if}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.sire.sire.name|escape:url}">
					{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.dam.sire.sire.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.dams} {$msgs.sires} {$msgs.sire}</span>
					{/if}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.sire.dam.name|escape:url}">
					{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.dam.sire.dam.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.dams} {$msgs.sires} {$msgs.dam}</span>
					{/if}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.dam.sire.name|escape:url}">
					{if $icon_sire}<img src="{external file=$icon_sire}" alt="" />{/if}{$data.dam.dam.sire.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.dams} {$msgs.dams} {$msgs.sire}</span>
					{/if}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.dam.dam.name|escape:url}">
					{if $icon_dam}<img src="{external file=$icon_dam}" alt="" />{/if}{$data.dam.dam.dam.name|capitalize}
					{if $show_label}
						<br /><span>{$msgs.dams} {$msgs.dams} {$msgs.dam}</span>
					{/if}
				</a>
			</li>
		</ul>
	{/if}
	{if $maxGen>3}
		<ul class="pedigree_gen4">
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.sire.sire.sire.name|escape:url}">
					{$data.sire.sire.sire.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.sire.sire.dam.name|escape:url}">
					{$data.sire.sire.sire.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.sire.dam.sire.name|escape:url}">
					{$data.sire.sire.dam.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.sire.dam.dam.name|escape:url}">
					{$data.sire.sire.dam.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.dam.sire.sire.name|escape:url}">
					{$data.sire.dam.sire.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.dam.sire.dam.name|escape:url}">
					{$data.sire.dam.sire.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.sire.dam.dam.sire.name|escape:url}">
					{$data.sire.dam.dam.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.sire.dam.dam.dam.name|escape:url}">
					{$data.sire.dam.dam.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.sire.sire.sire.name|escape:url}">
					{$data.dam.sire.sire.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.sire.sire.dam.name|escape:url}">
					{$data.dam.sire.sire.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.sire.dam.sire.name|escape:url}">
					{$data.dam.sire.dam.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.sire.dam.dam.name|escape:url}">
					{$data.dam.sire.dam.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.dam.sire.sire.name|escape:url}">
					{$data.dam.dam.sire.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.dam.sire.dam.name|escape:url}">
					{$data.dam.dam.sire.dam.name|capitalize}
				</a>
			</li>
			<li class="sire">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_sire]={$data.dam.dam.dam.sire.name|escape:url}">
					{$data.dam.dam.dam.sire.name|capitalize}
				</a>
			</li>
			<li class="dam">
				<a href="{$classifieds_file_name}?a=19&amp;b[pedigree_dam]={$data.dam.dam.dam.dam.name|escape:url}">
					{$data.dam.dam.dam.dam.name|capitalize}
				</a>
			</li>
		</ul>
	{/if}
	<div class="clear"><br /></div>
</div>
