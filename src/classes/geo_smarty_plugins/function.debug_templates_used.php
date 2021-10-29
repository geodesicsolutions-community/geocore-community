<?php
//function.debug_templates_used.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//This fella takes care of {debug_templates_used ...}

function smarty_function_debug_templates_used ($params, Smarty_Internal_Template $smarty)
{
	//do it in HTML
	$templates = geoTemplate::getLoggedTemplates();
	?>
<fieldset>
	<legend>DEBUG - Templates Used</legend>
	<div>
		<p>
			<strong>Notes & Tips:</strong>
			<ol>
				<li>This is meant primarily for developers or designers familiar with Geo design and Smarty templates.</li>
				<li>Templates are listed in the order they were loaded by the software.</li>
				<li>Some templates may be repeated below, that is because they were used more than once.</li>
				<li>Turn your <strong>cache off</strong>, as the template list will not include templates in sections loaded from cache.
				<li><span style="color: red;">Do not</span> edit templates in the default template set!</li>
				<li>Make sure to put the {debug_templates_used} tag at the end of your templates, as it will only list templates used up to that point in time.  See a few notes down for a suggestion of where you could add it, and trick on how to make it only show if you have ?debug=stats in URL.</li>
				<li>If you see a system or module template ending in "folder/index", that could be either "folder/index.tpl" OR "folder.tpl" (the latter is most common for module templates).  It is just how the system works. (it would add unnecessary steps, which would impact performance, to figure out the "generated" file name every time.)</li>
				<li>Recommended instructions for debugging on "live" sites:
					<ol type="a">
						<li>Add this to <strong>footer.tpl</strong> right before the &lt;/body&gt; tag:
							<pre>{if $smarty.get.debug||$smarty.cookie.debug}{debug_templates_used}{/if}</pre>
						</li>
						<li>Turn on the show debug messages addon.</li>
						<li>View index.php?debug=stats on the site to see debug messages, db stats, and this list of templates.</li>
						<li>Browse to the page you want to see what templates are loaded for it.</li>
					</ol>
				</li>
				<li>As always, when editing <strong>system or module</strong> templates, follow <a href="http://geodesicsolutions.com/support/wiki/tutorials/design_adv/replace_system_templates" onclick="window.open(this.href); return false;" style="text-decoration: underline;">linked instructions</a>.</li>
			</ol>
		</p>
		<strong>Template Files Used:</strong> (only up until this point in templates)
		<ul>
			<li><strong>Legend: </strong> [TEMPLATE SET] / [TEMPLATE TYPE] / path/to/template.tpl</li>
			<?php foreach ($templates as $tpl) { ?>
				<li>[<?php echo $tpl['t_set']; ?>] / [<?php echo $tpl['type']; ?>] / <?php echo $tpl['file']; ?></li>
			<?php } ?>
		</ul>
	</div>
</fieldset>
	<?php 
}
