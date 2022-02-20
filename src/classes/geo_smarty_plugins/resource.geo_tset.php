<?php
/**
 * This handy resource is responsible for figuring out just "where" a resource
 * is located, by looking through the active template sets, etc. etc.
 */

class Smarty_Resource_Geo_tset extends Smarty_Resource
{
	//Set this to true to make compiled template filenames more human readable
	const debugCompiled = true;

    /**
     * Parse the path for the g_type, g_resource, and name
     *
     * @param string $path
     * @return ?array
     */
    private function getDetailsFromPath($path)
    {
        $details = [
            'g_type' => '',
            'g_resource' => '',
            'name' => '',
        ];

        $parts = array_values(array_filter(explode('/', $path)));
        if (!in_array($parts[0], geoTemplate::VALID_G_TYPES) || count($parts) < 2) {
            // path does not have what we need, the first part is not a g_type or there is not enough parts
            return null;
        }
        $details['g_type'] = array_shift($parts);
        $details['g_resource'] = count($parts) > 1 ? array_shift($parts) : '';
        $details['name'] = implode('/', $parts);
        return $details;
    }

    /**
     * Parse an indexed name, it will be like default:system:shared:tplname
     *
     * @param string $name
     * @return ?array
     */
    private function getDetailsFromIndex($name)
    {
        $details = [
            'g_type' => '',
            'g_resource' => '',
            'name' => '',
        ];

        $parts = explode(':', $name);

        if (count($parts) < 3 || count($parts) > 4) {
            // wrong format
            return null;
        }
        // discard the first part, it will be the template set name but we always get the name again
        array_shift($parts);

        $details['g_type'] = array_shift($parts);
        if (!in_array($details['g_type'], geoTemplate::VALID_G_TYPES)) {
            return null;
        }
        $details['g_resource'] = count($parts) > 1 ? array_shift($parts) : '';
        $details['name'] = array_shift($parts);
        return $details;
    }

    private function getDetailsFromTemplate(Smarty_Template_Source $source, Smarty_Internal_Template $_template)
    {
        $details = [
            'g_type' => '',
            'g_resource' => '',
            'name' => '',
        ];
        /**
         * First, check in template vars, if it is set in a template var that means
         * (most likely) that it was set in an include smarty tag.  When that happens,
         * the template var will remain "local" until that template is done, then
         * when it gets back to the parent, that template var will no longer be
         * there...
         */
        if ($_template->ext) {
            // it will only work if far enough along to have ext set
            $details['g_type'] = $_template->getTemplateVars('g_type');
            $details['g_resource'] = $_template->getTemplateVars('g_resource');
        }

        /**
         * Now, if no template vars were set for g_type or g_resource, get the
         * value from the template object.
         */
        if (empty($details['g_type']) && $_template instanceof geoTemplate) {
            $details['g_type'] = $_template->gType();
        }
        if (empty($details['g_resource']) && $_template instanceof geoTemplate) {
            $details['g_resource'] = $_template->gResource();
        }

        /**
         * Now try smarty->smarty if still no luck with others
         */
        if (empty($details['g_type']) && $_template->smarty instanceof geoTemplate) {
            $details['g_type'] = $_template->smarty->gType();
        }
        if (empty($details['g_resource']) && $_template->smarty instanceof geoTemplate) {
            $details['g_resource'] = $_template->smarty->gResource();
        }

        /**
         * Last but not least, try out parent
         */
        if (empty($details['g_type']) && $_template->parent instanceof geoTemplate) {
            $details['g_type'] = $_template->parent->gType();
        }
        if (empty($details['g_resource']) && $_template->parent instanceof geoTemplate) {
            $details['g_resource'] = $_template->parent->gResource();
        }

        $details['name'] = $source->name;
        return $details;
    }

    /**
     * Parse the g_type, g_resource out of the details as best as possible
     *
     * @param Smarty_Template_Source $source
     * @param Smarty_Internal_Template|null $_template
     * @return array
     */
    private function getDetails(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null)
    {
        // First: see if we can get it from the path alone
        $details = $this->getDetailsFromPath($source->name);
        if ($details) {
            return [$details['g_type'], $details['g_resource'], $details['name']];
        }
        $details = $this->getDetailsFromIndex($source->name);
        if ($details) {
            return [$details['g_type'], $details['g_resource'], $details['name']];
        }
        if ($_template !== null) {
            $details = $this->getDetailsFromTemplate($source, $_template);
            if ($details) {
                return [$details['g_type'], $details['g_resource'], $details['name']];
            }
        }
        // could not get anything, try sensible defaults maybe they will work
        return [
            defined('IN_ADMIN') ? geoTemplate::ADMIN : geoTemplate::MAIN_PAGE,
            '',
            $source->name,
        ];
    }

	/**
	 * populate Source Object with meta data from Resource
	 *
	 * @param Smarty_Template_Source   $source    source object
	 * @param Smarty_Internal_Template $_template template object
	 */
	public function populate (Smarty_Template_Source $source, Smarty_Internal_Template $_template=null)
	{
		list($g_type, $g_resource, $name) = $this->getDetails($source, $_template);

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
		if ($source instanceof Smarty_Template_Config) {
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
			$_filepath = substr($_filepath, 0, 2) . '/'
				. substr($_filepath, 2, 2) . '/'
				. substr($_filepath, 4, 2) . '/'
				. $_filepath;
		}
		$_compile_dir_sep = $_template->smarty->use_sub_dirs ? '/' : '^';
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
			$_compile_dir .= '/';
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
	public function buildUniqueResourceName (Smarty $smarty, $resource_name, $is_config = false)
	{
		return get_class($this) . '#' . $smarty->joined_template_dir . '#' . $smarty->gType() . '#'
            . $smarty->gResource() . '#' . $resource_name;
	}
}
