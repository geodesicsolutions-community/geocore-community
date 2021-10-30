<?php
//Cache.inc.php
/**
 * This is a proceedural file, designed to emulate including a cache file, no
 * matter what type of cache is being used, and whether the file has actually
 * been written to the file yet.
 * 
 * If it has been written to a file, it will actually include the file, so that
 * using this will be much more efficient than getting the contents and evaling
 * (which is required if that is not the case)
 * 
 * @package System
 * @since Version 4.0.0 (previous versions used other methods to include cache files)
 * @return mixed Returns just like actually including the file would have
 *  returned, or false if there was a cache miss or error reading cache.
 */



/**
 * Used to include a cache file.  Before this file is included,
 * be sure that the var $filename is set to the
 * cache file you want included.
 */
if (!isset($filename)) {
	return false;
}
if (GEO_CACHE_STORAGE == 'memcache'){
	$txt = geoCache::read($filename);

	if ($txt === false){
		//actually returned boolean false, which means read failed for some reason.
		return false;
	}

	//simulate what would happen if the file
	//was included.. Not that efficient (or can even be potentially dangerous on shared servers
	//I would imagine) but if you know of a better
	//way, we're all ears ;-)
	return eval ('?'.'>'.$txt);
}
if (geoCache::file_exists($filename)) {
	if (isset(geoCache::$files[$filename])) {
		//it is not saved yet, but it is cached locally
		return eval('?'.'>'.geoCache::$files[$filename]['contents']);
	}
	//do a normal include
	$result = require CACHE_DIR . $filename;
	return $result;
}
return false;
