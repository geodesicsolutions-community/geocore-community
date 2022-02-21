<?php

//this fella makes it so that $template->_cache['used_tags'] is populated with just the info
//we need, without having to re-compile templates every time.

function smarty_postfilter_process_tags ($source, Smarty_Internal_Template $template)
{
	$extra = array (
		'include' => array(),
		'addon' => array(),
		'module' => array(),
	);
	$addSection = false;
	foreach ($template->_cache['used_tags'] as $tag) {
		if (!in_array($tag[0], array('addon','include','module','listing'))) {
			//not addon or include tag
			continue;
		}

		$vars = array();
		foreach ($tag[1] as $var) {
			foreach ($var as $name => $val) {
				$vars[$name] = trim($val,"'\"");
			}
		}
		if ($tag[0]=='include') {
			//go through includes as well
			$info = array();

			$info['file'] = $vars['file'];
			if (strpos($info['file'],'".')!==false) {
				//This is "dynamic" included file with embedded part to it, not possible
				//to pre-load in this case
				continue;
			}

			if (isset($vars['g_type'])) {
				$info['g_type'] = $vars['g_type'];
			}
			if (isset($vars['g_resource'])) {
				$info['g_resource'] = $vars['g_resource'];
			}
			//make key such that if same include is used multiple times, it is still
			//only added to the array once
			$extra['include'][implode(':',$info)] = $info;
			unset ($info);
		} else if ($tag[0]=='module') {
			if (isset($vars['tag']) && $vars['tag']) {
				$extra['module'][$vars['tag']] = $vars['tag'];
			}
		} else if ($tag[0] == 'addon' || $tag[0]=='listing') {
			if (!isset($vars['addon']) || !isset($vars['tag']) || $vars['addon']=='core') {
				//failsafe, required info missing, or this is a core tag
				continue;
			}
			//Note:  Do NOT check to see if addon is enabled and has the tag
			//able to be used at this point, or template would need to be re-compiled
			//every time addon is enabled/disabled

			$info = array();

			$info['addon'] = $vars['addon'];
			$info['tag'] = $vars['tag'];

			//make key such that if the same addon tag is used multiple times, it is
			//still only added to the array once
			$extra['addon'][implode(':',$info)] = $info;
			unset($info);
		}
		$addSection = true;
	}
	$template->mustCompile();
	if ($addSection) {
		//there is stuff to process

		$section = '<?php $_smarty_tpl->_cache[\'used_tags\'] = '.var_export($extra,1).'; ?>';

		$source = str_replace('/*/%%SmartyHeaderCode%%*/?>','/*/%%SmartyHeaderCode%%*/?>'.$section,$source);
	}
	return $source;
}
