{* 4eb7314 *}


<div class="pedigree_tree pedigree_input">
	<ul class="pedigree_gen1">
		<li class="sire">
			{assign var=fieldName value='b[pedigreeTree][sire]'}
			{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
			<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.name|escape|capitalize}" class="field" />
			{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
		</li>
		<li class="dam">
			{assign var=fieldName value='b[pedigreeTree][dam]'}
			{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
			<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.name|escape|capitalize}" class="field" />
			{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
		</li>
	</ul>
	{if $maxGen>1}
		<ul class="pedigree_gen2">
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][sire]'}
				{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][dam]'}
				{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire dam_sire">
				{assign var=fieldName value='b[pedigreeTree][dam][sire]'}
				{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][dam]'}
				{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
		</ul>
	{/if}
	{if $maxGen>2}
		<ul class="pedigree_gen3">
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][sire][sire]'}
				{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][sire][dam]'}
				{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][dam][sire]'}
				{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][dam][dam]'}
				{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][dam][sire][sire]'}
				{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][sire][dam]'}
				{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][dam][dam][sire]'}
				{if $icon_sire}<img src="{if $in_admin}../{/if}{external file=$icon_sire}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][dam][dam]'}
				{if $icon_dam}<img src="{if $in_admin}../{/if}{external file=$icon_dam}" alt="" />{/if}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<br /><span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
		</ul>
	{/if}
	{if $maxGen>3}
		<ul class="pedigree_gen4">
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][sire][sire][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][sire][sire][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][sire][dam][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][sire][dam][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.sire.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][dam][sire][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][dam][sire][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][sire][dam][dam][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][sire][dam][dam][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.sire.dam.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][dam][sire][sire][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][sire][sire][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][dam][sire][dam][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][sire][dam][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.sire.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][dam][dam][sire][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.sire.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][dam][sire][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.sire.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="sire">
				{assign var=fieldName value='b[pedigreeTree][dam][dam][dam][sire]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.dam.sire.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
			<li class="dam">
				{assign var=fieldName value='b[pedigreeTree][dam][dam][dam][dam]'}
				<input type="text" name="{$fieldName}[name]" size="14" value="{$data.dam.dam.dam.dam.name|escape|capitalize}" class="field" />
				{if $errors.$fieldName}<span class="error_message">{$errors.$fieldName}</span>{/if}
			</li>
		</ul>
	{/if}
	<div class="clear"><br /></div>
</div>
