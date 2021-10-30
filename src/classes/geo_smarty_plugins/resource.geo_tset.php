<?php
//resource.geo_tset.php


/**
 * This handy resource is responsible for figuring out just "where" a resource
 * is located, by looking through the active template sets, etc. etc.
 * 
 */


class Smarty_Resource_Geo_tset extends Smarty_Resource
{
	//Set this to true to make compiled template filenames more human readable
	const debugCompiled = true;
	
	/**
	 * populate Source Object with meta data from Resource
	 *
	 * @param Smarty_Template_Source   $source    source object
	 * @param Smarty_Internal_Template $_template template object
	 */
	public function populate (Smarty_Template_Source $source, Smarty_Internal_Template $_template=null)
	{
		//die ("source: <pre>".print_r($source,1));
		//Need to determine the g_type (geo template type: system, module, main_page, etc.)
		//and g_resource (the top level folder inside the g_type folder)
		if ($_template===null) {
			//figure it out based on parts of name
			$parts = explode(':',$source->name);
			$g_type = $parts[1];
			if (count($parts)==4) {
				$g_resource = $parts[2];
				$name = $parts[3];
			} else {
				$g_resource = '';
				$name = $parts[2];
			}
		} else {
			/*
			 * First, check in template vars, if it is set in a template var that means
			 * (most likely) that it was set in an include smarty tag.  When that happens,
			 * the template var will remain "local" until that template is done, then
			 * when it gets back to the parent, that template var will no longer be
			 * there...
			 */
			$g_type = $_template->getVariable('g_type',null,true,false)->value;
			$g_resource = $_template->getVariable('g_resource',null,true,false)->value;
			
			/*
			 * Now, if no template vars were set for g_type or g_resource, get the
			 * value from the template object.
			 */
			$g_type = ($g_type!==null)? $g_type : $_template->gType();
			$g_resource = ($g_resource!==null)? $g_resource : $_template->gResource();
			$name = $source->name;
		}
		
		//echo "getting for {$source->name} t:$g_type r:$g_resource name:{$name} now...<br />\n";
		$realPath = geoTemplate::getFilePath($g_type, $g_resource, $name);
		
		$tset = ($g_type=='admin')? 'admin' : geoTemplate::whichTemplateSet($g_type, $g_resource, $name);
		if ($_template !== null) {
			geoTemplate::logTemplateUsed($tset, $g_type, (($g_resource)?$g_resource.'/':'').$name);
		}
		$g_resource .= ($g_resource)? ':':'';
			
		//make file path unique to tset, type, resource, and filename
		$source->filepath = $source->type .':'.$tset.':'.$g_type.':'.$g_resource.$name;
		$source->uid = sha1($source->filepath);
		
		if ($realPath && $source->smarty->compile_check) {
			$source->timestamp = filemtime($realPath);
			$source->exists = !!$source->timestamp;
		}
	}
	
	/**
	 * Load template's source from file into current template object
	 *
	 * @param Smarty_Template_Source $source source object
	 * @return string template source
	 * @throws SmartyException if source cannot be loaded
	 */
	public function getContent (Smarty_Template_Source $source)
	{
		if ($source->timestamp) {
			$parts = explode(':',$source->filepath);
			
			$g_type = $parts[2];
			if (count($parts)==5) {
				$g_resource = $parts[3];
				$filename = $parts[4];
			} else {
				$g_resource = '';
				$filename = $parts[3];
			}
			//$g_resource = $parts[3];
			//$filename = $parts[4];
			
			return file_get_contents(geoTemplate::getFilePath($g_type, $g_resource, $filename));
		}
		if ($source instanceof Smarty_Config_Source) {
			throw new SmartyException("Unable to read config {$source->type} '{$source->name}'");
		}
		throw new SmartyException("Unable to read template {$source->type} '{$source->name}'");
	}
	
	/**
	 * populate Compiled Object with compiled filepath
	 *
	 * @param Smarty_Template_Compiled $compiled  compiled object
	 * @param Smarty_Internal_Template $_template template object
	 */
	public function populateCompiledFilepath (Smarty_Template_Compiled $compiled, Smarty_Internal_Template $_template)
	{
		$_compile_id = isset($_template->compile_id) ? preg_replace('![^\w\|]+!', '_', $_template->compile_id) : null;
		
		if (self::debugCompiled) {
			// calculate Uid if not already done... done by getTemplateFilepath()
			$parts = explode(':',$compiled->source->filepath);
			array_pop($parts);
		} else {
			//not debugging compiled files, so don't bother making them easy to follow
			$parts = array($compiled->source->type);
		}
		
		$_filepath = $compiled->source->uid;
		// if use_sub_dirs, break file into directories
		if ($_template->smarty->use_sub_dirs) {
			$_filepath = substr($_filepath, 0, 2) . DS
				. substr($_filepath, 2, 2) . DS
				. substr($_filepath, 4, 2) . DS
				. $_filepath;
		}
		$_compile_dir_sep = $_template->smarty->use_sub_dirs ? DS : '^';
		if (isset($_compile_id)) {
			$_filepath = $_compile_id . $_compile_dir_sep . $_filepath;
		}
		if ($_template->caching) {
			$_cache = '.cache';
		} else {
			$_cache = '';
		}
		$_compile_dir = $_template->smarty->getCompileDir();
		if (strpos('/\\', substr($_compile_dir, -1)) === false) {
			$_compile_dir .= DS;
		}
		
		$compiled->filepath = $_compile_dir . $_filepath . '.' . implode('.',$parts). '.' . basename($compiled->source->name) . $_cache . '.php';
	}
	
	/**
	 * modify resource_name according to resource handlers specifications
	 *
	 * @param Smarty $smarty        Smarty instance
	 * @param string $resource_name resource_name to make unique
	 * @return string unique resource name
	 */
	protected function buildUniqueResourceName (Smarty $smarty, $resource_name)
	{
		return get_class($this) . '#' . $smarty->joined_template_dir . '#' . $smarty->gType() . '#' . $smarty->gResource() . '#' . $resource_name;
	}
}
