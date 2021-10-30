{* 7.5.3-36-gea36ae7 *}
{foreach $columns as $column}
	<ul class="extraCheckboxes columns-{$colCount}">
		{foreach $column as $c}
			<li>{$c}</li>
		{/foreach}
	</ul>
{/foreach}