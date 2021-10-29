<?php
//File.class.php
/**
 * Holds the geoFile class, which is responsible for file-based operations.
 * 
 * @package System
 * @since Version 5.0.0
 */
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.5.3-36-gea36ae7
## 
##################################

/**
 * As the name implies, this is used for file-based utility methods.
 * 
 * @package System
 * @since Version 5.0.0
 */
class geoFile
{
	/**
	 * Internal
	 * @internal
	 */
	private $_doChmod, $_jailToDir, $_umask;
	/**
	 * Stores the instance of geoFile used Singleton method
	 * @var geoFile
	 */
	private static $_instance;
	
	const BASE = 'default';
	
	const TEMPLATES = 'templates';
	
	const CACHE = 'cache';
	
	const ADDON = 'addon';
	
	/**
	 * This is array of files/folders to ignore when getting array of files at a location.
	 * It contains the obvious . (current dir), .. (parent dir), and all the
	 * folder names added by frontpage extensions.
	 * 
	 * @var array
	 */
	private static $_ignoreFiles = array (
		'.',
		'..',
		//frontpage extensions folders
		'_vti_cnf',
		'_vti_pvt',
		'_vti_script',
		'_vti_txt',
		//added by dreamweaver
		'_notes'
	);
	
	/**
	 * Gets an instance, you can specify a different "name" such as "templates"
	 * or whatever you want, so that multiple instances of the geoFile class can
	 * exist, with different jailed dirs.
	 * 
	 * Built in instances:
	 * geoFile::BASE - used if nothing specified
	 * geoFile::TEMPLATES = jail to GEO_TEMPLATE_DIR
	 * geoFile::CACHE = jail to CACHE_DIR (not used yet)
	 * 
	 * 
	 * @param string $instance
	 * @param bool $initChmod if false, will skip checking whether to CHMOD 777
	 *   the files or not.
	 * @return geoFile
	 */
	public static function getInstance ($instance = 'default', $initChmod = true)
	{
		if (!isset(self::$_instance[$instance]) || !is_object(self::$_instance[$instance])) {
			$c = __class__;
			self::$_instance[$instance] = new $c ($instance, $initChmod);
		}
		return self::$_instance[$instance];
	}
	
	/**
	 * Cleans the path, gets rid of any ".", "..", and consecutive /, changes the
	 * dir seperator to /.
	 * 
	 * Path does not need to be inside "jailed" location (unlike other geoFile
	 * methods)
	 * 
	 * @param string $path
	 * @return string
	 */
	public static function cleanPath ($path)
	{
		//DO NOT init!
		$path = trim($path);
		if (self::isWindows()){
			$path = str_replace('\\', '/', $path);
			if (preg_match('|^[a-zA-Z]{1}\:/|', $path, $matches)) {
				$start = $matches[0];
			} else {
				$start = (substr($path,0,1) == '/')? '/' : '';
			}
			//convert drive letter (X:/) to just / on windows systems, it will be
			//added back by start
			$path = preg_replace('|^[a-zA-Z]{1}\:/|','/',$path);
		} else {
			$start = (substr($path,0,1) == '/')? '/' : '';
		}
		
		$end = (substr($path, -1) == '/')? '/' : '';
		
		$parts = array_filter(explode('/', $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			//TODO: Check to make sure this works on different charset encoding!
			if ($part == '.') continue;
			
			if ($part == '..') {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		//make sure to NOT return just //
		if (!count($absolutes) && $start && $end) {
			//nothing in middle, and there is start & end, so to
			//prevent returning "//" force it to return only start.
			return $start;
		}
		
		return $start.implode('/', $absolutes).$end;
	}
	
	/**
	 * Internal method, initializes static vars for use in the different methods.
	 * 
	 * This is private on purpose, it should only be created by itself
	 * 
	 * @param string $instance
	 * @param bool $initChmod
	 */
	private function __construct ($instance, $initChmod = true)
	{
		//jail all file operations to happen in a sub-directory of GEO_BASE_DIR
		//by default.
		switch ($instance) {
			case self::TEMPLATES:
				$this->jailTo(GEO_TEMPLATE_DIR);
				break;
				
			case self::CACHE:
				$this->jailTo(CACHE_DIR);
				break;
				
			case self::ADDON:
				$this->jailTo(ADDON_DIR);
				break;
				
			case self::BASE:
			default:
				$this->jailTo(GEO_BASE_DIR);
		}
		
		if ($initChmod) {
			$this->_doChmod = DataAccess::getInstance()->get_site_setting('useCHMOD');
		}
	}
	
	/**
	 * Allow setting whether or not to chmod 777 a file/folder, call this before
	 * calling other stuff, if you need to use setting other than what is set in DB
	 * 
	 * @param bool $chmod
	 * @return geoFile Return this for method chaining (as of version 5.2.0)
	 */
	public function setChmod ($chmod)
	{
		$chmod = (bool)$chmod;
		$this->_doChmod = $chmod;
		return $this;
	}
	
	/**
	 * Specify what directory all geoFile operations must be "jailed" to.  If any
	 * operations done through the geoFile class are attempted on a location
	 * outside the jailTo directory, the operation will fail and admin error thrown.
	 * 
	 * Note that if this method is not called prior to using another geoFile
	 * method, it will default to be "jailed" to the GEO_BASE_DIR.
	 * 
	 * @param string $dir Absolute directory to jail to
	 * @return geoFile Return this for method chaining (as of version 5.2.0)
	 */
	public function jailTo ($dir)
	{
		$dir = self::cleanPath($dir);
		if (!self::_isAbsolute($dir)) {
			//oops!  what?  This shouldn't really happen unless dir in config is set wrong
			throw new Exception ('Absolute directory ('.$dir.') is not absolute!');
		}
		if ($dir) {
			$this->_jailToDir = $dir;
		}
		return $this;
	}
	
	/**
	 * Whether or not to chmod to 0777
	 * @return bool
	 */
	public function useChmod()
	{
		return $this->_doChmod;
	}
	
	/**
	 * Figured out if the given parent directory is really a parent of the given
	 * child directory.
	 * 
	 * If the parent is not a child of the current jailed directory, this check
	 * will automatically fail.  {@link geoFile::jailTo()}
	 * 
	 * @param string $parent The parent directory.  Either absolute location, 
	 *   or relative to the current "jailed" directory.
	 * @param string $child The child directory.  Either absolute location, or
	 *   relative to the current "jailed" directory.
	 * @return bool
	 */
	public function isChild ($parent, $child)
	{
		$parent = $this->absolutize($parent);
		$child = $this->absolutize($child);
		
		if ($parent !== $this->_jailToDir) {
			//check to make sure parent is sub of jailed dir
			if (!$this->isChild($this->_jailToDir, $parent)) {
				return false;
			}
		}
		
		return ($parent && $child && substr($child, 0, strlen($parent)) === $parent);
	}
	
	/**
	 * Figures out whether or not the given location is "in jail" or not.  If in
	 * admin panel, adds an admin error message.  {@link geoFile::jailTo()}
	 * 
	 * @param string $location Either absolute location, or relative to the
	 *   current "jailed" directory.
	 * @return bool
	 */
	public function inJail ($location)
	{
		if (!$this->isChild($this->_jailToDir, $location)) {
			//not in jail!
			$this->_adminError('The file ('.$location.') is "outside" the allowed directory, not able to proceed.');
			return false;
		}
		return true;
	}
	
	/**
	 * Takes the path, runs it through {@link geoFile::cleanPath()}, and if it
	 * does not already start with / it prepends it with the current "jailed to" dir.
	 * 
	 * Path does not need to be inside "jailed" location (unlike other geoFile
	 * methods)
	 * 
	 * @param string $path Either absolute location, or relative to the
	 *   current "jailed" directory.
	 * @return string The absolute path.
	 */
	public function absolutize ($path)
	{
		$path = self::cleanPath($path);
		
		if (!self::_isAbsolute($path)) {
			//if not absolute already, then prepend it with the current jailed dir...
			$start = (isset($this->_jailToDir))? $this->_jailToDir : GEO_BASE_DIR;
			$path = $start.$path;
		}
		return $path;
	}
	
	/**
	 * Internal method used to determine if the path is already absolute or not.
	 * Since the path is assumed to already be "cleaned" it is not suitable for
	 * use outside of this class.  This takes into account windows systems and
	 * having a:/ at front
	 * 
	 * @param string $path The already-cleaned path to check
	 * @return bool
	 * @since Version 7.1.0
	 */
	private static function _isAbsolute ($path)
	{
		if (self::isWindows() && preg_match('|^[a-zA-Z]{1}\:/|', $path)) {
			//It is windows, and beginning looks something like a:/ so yes, treat
			//it as absolute already
			return true;
		}
		//check if first string is / or not
		return (substr($path,0,1) === '/');
	}
	
	/**
	 * Kind of like {@link copy()} but can be used on a directory to copy the
	 * entire directory's contents to another location.
	 * 
	 * The paths in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * @param string $from The file or directory "from" location.    Either
	 *   absolute location, or relative to the current "jailed" directory.
	 * @param string $to The file or directory "to" location.    Either
	 *   absolute location, or relative to the current "jailed" directory.
	 * @param bool $emptyDirs If true, will create empty directories.
	 * @return bool True on success, false on failure.  If failure, an admin
	 *   message is generated about what did not work.
	 */
	public function copy ($from, $to, $emptyDirs = false)
	{
		$from = $this->absolutize($from);
		$to = $this->absolutize($to);
		
		//make sure from and to are both children of jailed dir
		if (!$this->inJail($from) || !$this->inJail($to)) {
			//the from or to is outside where we are supposed to be, cannot proceed
			return false;
		}
		if (is_dir($from)) {
			if ($emptyDirs && !$this->mkdir($to)) {
				//could not create destination directory!
				return false;
			}
			$dirlist = array_diff(scandir($from), self::$_ignoreFiles);
			
			foreach ($dirlist as $entry) {
				$fromFile = $from . '/' . $entry;
				$toFile = $to . '/' . $entry;
				//recursively copy everything in this dir
				if (!$this->copy($fromFile, $toFile, $emptyDirs)) {
					//stop if it failed to copy something
					return false;
				}
			}
		} else {
			if (!$this->mkdir(dirname($to))) {
				return false;
			}
			if (! copy ($from, $to)) {
				//let user know about the error.
				$this->_adminError("Error while attempting to copy ($from) to ($to) - copy has failed.");
				return false;
			}
			if ($this->useChmod()) {
				chmod($to,0777);
			}
		}
		return true;
	}
	
	/**
	 * Kind of like {@link rename()} but does some checks and makes sure the
	 * to's parent directory exists before it attempts to re-name.
	 * 
	 * The paths in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * @param string $from directory/file location.  Either absolute location,
	 *   or relative to the current "jailed" directory.
	 * @param string $to Absolute directory/file location.  Either absolute
	 *   location, or relative to the current "jailed" directory.
	 * @return bool true on success, false on failure.  If failure, it will
	 *   generate an admin error message as well.
	 */
	public function rename ($from, $to)
	{
		$from = $this->absolutize($from);
		$to = $this->absolutize($to);
		
		if (!$this->inJail($from) || !$this->inJail($to)) {
			//the from or to is outside where we are supposed to be, cannot proceed
			return false;
		}
		
		if (!$this->mkdir(dirname($to))) {
			return false;
		}
		if (! rename ($from, $to)) {
			//let user know about the error.
			$this->_adminError("Error while attempting to rename ($from) to ($to) - rename has failed.");
			return false;
		}
		if ($this->useChmod()) {
			chmod($to,0777);
		}
		return true;
	}
	/**
	 * Used internally
	 * @internal
	 */
	private static $_dirsCreated = array();
	
	/**
	 * Makes the given directory (parents as well).
	 * 
	 * The paths in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * @param string $dir Either absolute location, or relative to the current
	 *   "jailed" directory.
	 * @return bool true on success, false on failure.  If failure, it will
	 *   generate an admin error message as well.
	 */
	public function mkdir ($dir)
	{
		//make sure the dir ends in a / so it doesn't fail inJail check
		if (substr($dir,-1,1) != '/') $dir .= '/';
		
		$dir = $this->absolutize($dir);
		
		if (!$this->inJail($dir)) {
			//not where we are supposed to be
			return false;
		}
		
		if (isset(self::$_dirsCreated[$dir])) {
			//keep us from having to create the directory a billion times..
			return self::$_dirsCreated[$dir];
		}
		
		//see if the dir exists first, we might not need to create it
		if (is_dir($dir) && is_writable($dir)) {
			self::$_dirsCreated[$dir] = true;
			return true;
		}
		
		if ($this->useChmod() && !$this->_umask) {
			//change umask if needed
			$this->_umask = true;
			umask(0);
		}
		
		//recursively create directory, setting the chmod as we go
		mkdir ($dir, 0777, true);
		
		//see if the dir exists now
		if (is_dir($dir) && is_writable($dir)) {
			self::$_dirsCreated[$dir] = true;
			return true;
		}
		//is not a dir or is not writable, either way it's bad
		$this->_adminError('Error creating directory ('.$dir.') - cannot continue with action.  Check file/directory permissions and try again. (Failed post creation "exists" and "writable" checks)');
		return false;
	}
	
	/**
	 * Pushes the given file to the browser.
	 * 
	 * The path in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * @param string $filename  Either absolute location, or relative to the
	 *   current "jailed" directory.
	 * @return bool true on success, false on failure.  If failure, it will
	 *   generate an admin error message as well.
	 */
	public function download ($filename)
	{
		$filename = $this->absolutize($filename);
		
		if (!$filename || !$this->inJail($filename)) {
			//file is not where it is supposed to be
			return false;
		}
		
		if (!file_exists($filename)) {
			$this->_adminError('File download not found ('.$filename.'), not able to download.');
			return false;
		}
		
		header('Content-Description: File Transfer');
		//TODO: ability to change content type depending on file type
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($filename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		ob_clean();
		flush();
		readfile($filename);
		return true;
	}
	
	/**
	 * Gives you an array of the contents of the given directory and optionally,
	 * sub-directories, with the key == index for easier management.
	 * 
	 * The paths in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * @param string $dir Either absolute location, or relative to the current 
	 *   "jailed" directory.
	 * @param bool $recursive Whether to parse sub-directories recursively
	 * @param bool $excludeDirs If false, will include entry for each directory itself.
	 * @param bool $onlyDirs If true, will only have entries that are directories.
	 * @param int $sorting_order See sorting_order var used in build-in scandir() PHP function
	 * @param string $localBase Should not be used, this is used for recuriviness..
	 * @return array
	 */
	public function scandir ($dir, $recursive=true, $excludeDirs=true, $onlyDirs=false, $sorting_order=0, $localBase = '')
	{
		if (!$this->inJail($dir)) {
			//not good location
			return false;
		}
		$dir = $this->absolutize($dir);
		
		if (substr($dir, -1) !== '/') {
			//dir needs to end in /
			$dir .= '/';
		}
		
		$fileList = array_diff(scandir($dir, $sorting_order), self::$_ignoreFiles);
		
		$list = array();
		foreach ($fileList as $file) {
			if ((is_dir($dir.$file) && !$excludeDirs) || (!is_dir($dir.$file) && !$onlyDirs)) {
				$list[$localBase.$file] = $localBase.$file;
			}
			
			if ($recursive && is_dir($dir.$file)) {
				$list = array_merge($list, $this->scandir("$dir{$file}/", $recursive, $excludeDirs, $onlyDirs, $sorting_order, $localBase.$file.'/'));
			}
		}
		
		return $list;
	}
	
	/**
	 * Used by places to clear all the sub-folders of wherever.
	 * This is DANGEROUS!  Carefull what dir you are sicking this thing on!
	 * 
	 * The path in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 *
	 * @param string $filename  Either absolute location, or relative to the
	 *   current "jailed" directory.
	 * @return bool Whether file seems to have been removed or not
	 */
	public function unlink($filename)
	{
		if (!$this->inJail($filename)) {
			//not in the location it is supposed to be!
			return false;
		}
		$filename = $this->absolutize($filename);
		
		if (is_dir($filename)) {
			//Note: don't use self::$_ignoreFiles here as we need ALL files to remove
			//them, we shouldn't ignore frontpage folders.
			$dirlist = array_diff(scandir($filename), array('.', '..'));
			foreach ($dirlist as $entry) {
				$this->unlink($filename.'/'.$entry);
			}
			//remove the directory
			return rmdir ($filename);
		} else {
			//just a normal file, attempt to delete it
			
			return unlink ($filename);
		}
	}
	
	/**
	 * Writes the given contents to the given file, making checks along the way
	 * in case there are problems, so that an accurate error message can be
	 * shown in the case of a problem.
	 * 
	 * The path in this method must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * As of version 7.3.0, if $use_lock is true (default value), will obtain
	 * an exclusive lock (write lock) on the file prior to writing to it.
	 * 
	 * @param string $file Either absolute location, or relative to the
	 *   current "jailed" directory.
	 * @param string $contents
	 * @param bool $use_lock if false, will NOT try to lock the file when writing.
	 *   Parameter added in version 7.3.0
	 * @return bool true on success, false on failure.  If failure, it will
	 *   generate an admin error message as well.
	 */
	public function fwrite ($file, $contents, $use_lock = true)
	{
		if (!$this->inJail($file)) {
			//oops! not in jail!
			return false;
		}
		$file = $this->absolutize($file);
		
		if (!$this->mkdir(dirname($file))) {
			//mkdir failed...
			return false;
		}
		//write the file
		
		if (file_exists($file) && !is_writable($file)) {
			$this->_adminError('Could not edit the existing file ('.$file.'), check file permissions (CHMOD 777) and try again.');
			return false;
		}
		
		if (!$handle = fopen($file,'w')) {
			$this->_adminError('An error occurred when attempting to write the file ('.$file.'), check file permissions (CHMOD 777) and try again.');
			return false;
		}
		if ($use_lock && !flock($handle, LOCK_EX)) {
			//flock does not work on all systems, for now "quietly" ignore the error
			$use_lock = false;
		}
		// Write $somecontent to our opened file.
		if (fwrite($handle, $contents) === false) {
			$this->_adminError("Cannot write to file ($file)");
			if ($use_lock) {
				flock($handle, LOCK_UN);
			}
			return false;
		}
		if ($use_lock) {
			//flush the output and release the lock
			fflush($handle);
			flock($handle, LOCK_UN);
		}
		fclose($handle);
		
		if ($this->useChmod()) {
			chmod($file, 0777);
		}
		return true;
	}
	
	/**
	 * Works just like the PHP function file_get_contents(), but relative to the
	 * currently jailed folder, or absolute location as long as it is within
	 * the jailed folder.
	 * 
	 * @param string $filename
	 * @since Version 5.1.1
	 */
	public function file_get_contents($filename)
	{
		if (!$this->inJail($filename)) {
			//oops! not in jail!
			return '';
		}
		$filename = $this->absolutize($filename);
		return file_get_contents($filename);
	}
	
	/**
	 * Generates a new name, useful for coming up with a re-name for something
	 * that already exists, like a template set.
	 * 
	 * The path in this method (if specified) must be inside the current "jailed" directory,
	 * or the operation will fail and an admin error thrown. {@link geoFile::jailTo()}
	 * 
	 * @param string $name The current (already used) name
	 * @param string $parentDir The parent directory (WITH trailing slash) that
	 *   the new name will be used in, uses this to check to make sure it
	 *   creates a name not already in use.  If not specified, assumes the current
	 *   jailed dir.  If it is specified, either absolute location, or relative
	 *   to the current "jailed" directory.
	 * @param bool $appendDate If true, will append the date to the name YYYY-MM-DD
	 * @return string The rename that is not already in use in the given parent dir.
	 */
	public function generateRename ($name, $parentDir=null, $appendDate = true)
	{
		if ($parentDir === null) {
			//default to use template dir
			$parentDir = $this->_jailToDir;
		} else {
			$parentDir = $this->absolutize($parentDir);
		}
		
		if (!$this->inJail($parentDir)) {
			//oops! not in jailed dir!
			return false;
		}
		
		$name = trim($name);
		if (!strlen($name)) {
			//sanity check, not meant to work with empty strings!
			return '';
		}
		/*
		 * First, get rid of any extra text that may have previously been added
		 * as the result of a previous re-name.  This would get rid of anything
		 * matching either:
		 *  _YYYY-MM-DD_# (at the end)
		 *  _# (at the end)
		 * 
		 * This is done to prevent something like a rename of:
		 * name_2009-10-20_1_2009-10-20_1
		 * if something that is already re-named is put through here.
		 */
		$before = $name;
		$name = preg_replace('/^(.+?)\_([0-9]{4}-[0-9]{2}-[0-9]{2}\_)?[0-9]+$/','$1',$name);
		//die ("before: $before<br />After: $name");
		if (!strlen($name)) {
			//oops the preg replace removed everything?  Restore the original...
			$name = $before;
		}
		
		if ($appendDate) {
			$name .= date('_Y-m-d');
		}
		$count = 1;
		do {
			$newName = "{$name}_$count";
			$count++;
		} while (file_exists($parentDir.$newName));
		return $newName;
	}
	
	/**
	 * Add the array of source files to the specified destination zip file.  The
	 * file structure in the zip file will be relative to the current jailed folder.
	 * 
	 * @param array $sources Array of files and folders, relative to the current jailed
	 *   folder, to add to the zip archive.
	 * @param string $destination The location of the zip archive, relative to the
	 *   current jailed folder.
	 * @return bool True if successful, false otherwise.
	 * @since Version 5.2.0
	 */
	public function zip ($sources, $destination)
	{
		$destination = $this->absolutize($destination);
		if (!is_array($sources)) {
			$sources = array($sources);
		}
		if (extension_loaded('zip')) {
			$zip = new ZipArchive;
			
			if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
				return false;
			}
			
			$startingSourceFolder = $this->_jailToDir;
			$startingSourceFolder = rtrim($startingSourceFolder,'/').'/';
			foreach ($sources as $entry) {
				$from = $this->absolutize($entry);
				if (is_dir($from)) {
					//add empty folder
					$folderName = str_replace($startingSourceFolder, '', rtrim($from, '/') . '/');
					if ($folderName!='/') {
						$zip->addEmptyDir($folderName);
					}
					//nifty trick to easily recurse into files/folders
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from), RecursiveIteratorIterator::SELF_FIRST);
					foreach ($files as $file) {
						if (is_dir($file)) {
							$zip->addEmptyDir(str_replace($startingSourceFolder, '', rtrim($file, '/').'/'));
						} else if (is_file($file)) {
							$zip->addFile($file, str_replace($startingSourceFolder,'',$file));
						}
					}
				} else if (file_exists($from)) {
					//add zipped file
					$zip->addFile($from, str_replace($startingSourceFolder,'',$from));
				}
			}
			return $zip->close();
		} else {
			//use PCL Zip
			require_once CLASSES_DIR . 'pclzip/pclzip.lib.php';
			
			$list = array();
			foreach ($sources as $source) {
				$file = $this->absolutize($source);
				if (file_exists($file)) {
					$list[] = $file;
				}
			}
			
			$archive = new PclZip($destination);
			$result = $archive->create($list, PCLZIP_OPT_REMOVE_PATH, rtrim($this->_jailToDir,'/'));
			
			if ($result==0) {
				die ("error: ".$archive->errorInfo(true));
			}
			
			return !($result==0);
		}
		return false;
	}
	
	/**
	 * Un-zips the given archived file to the given destination, the destination relative to
	 * the current jailed folder.
	 * 
	 * @param string $absZipFile The absolute location for the zip file to un-zip.  Note
	 *   that this CAN reside outside of the jailed folder, in order to allow
	 *   un-ziping files from the temp folder, for zip files uploaded by user.
	 * @param string $destination The destination folder, relative to the current
	 *   jailed folder.
	 * @return bool True if un-zip successful, false otherwise.
	 * @since Version 5.2.0
	 */
	public function unzip ($absZipFile, $destination)
	{
		$destination = rtrim($this->absolutize($destination),'/');
		if (extension_loaded('zip')) {
			$zip = new ZipArchive;
			
			if (!$zip->open($absZipFile)) {
				return false;
			}
			$destination .= '/';
			if (!$zip->extractTo($destination)) {
				return false;
			}
			return $zip->close();
		} else {
			//use pclzip as alternative way to attempt to un-zip
			//use PCL Zip
			require_once CLASSES_DIR . 'pclzip/pclzip.lib.php';
			
			$archive = new PclZip($absZipFile);
			$result = $archive->extract(PCLZIP_OPT_PATH, $destination);
			return !($result==0);
		}
	}
	
	/**
	 * Attempts to detect the mime type of the given file.
	 * 
	 * @param string $file File location to detect the file for.
	 * @param string $uploadName The name that is "reported" as the actual filename,
	 *   if different from the file being checked. Will be used to look up the
	 *   mime type according to extension if no other methods work to determine
	 *   the mime type.
	 * @param string $defaultMime The mime type set in file info when uploading
	 *   file, will be used if no other methods work to determine the mime type.
	 * @param bool $inJail If false, will not check to make sure the file is in
	 *   jail, for cases when the file is a temporary file like during file upload
	 * @return string|bool The mime type, or false on failure to detect mime type.
	 * @since Version 6.0.0
	 */
	public function getMimeType ($file, $uploadName = '', $defaultMime = '', $inJail = true)
	{
		if ($inJail) {
			//only absolutize location if it needs to be in jail
			$file = $this->absolutize($file);
			if (!$this->inJail($file)) {
				//file is not in jail!
				return false;
			}
		}
		
		//make sure file actually exists and we can work with it, just in case
		if (!$file || !file_exists($file)) {
			return false;
		}
		
		if (!is_readable($file)) {
			return false;
		}
		
		if (!$uploadName) {
			//Use file as the uploaded name
			$uploadName = $file;
		}
		$filename = ($uploadName)? $uploadName:$file;
		$extension = substr($filename, (strrpos($filename, '.')+1));
		
		//most uploads are images, so use getimagesize as it will hold the answers
		$imginfo_array = getimagesize($file);
		 
		if ($imginfo_array !== false) {
			//it is a valid image file
			$mime_type = (isset($imginfo_array['mime']))? $imginfo_array['mime']: false;
			if ($mime_type) {
				//that was easy!  return our findings!
				return $mime_type;
			}
		}
		
		//hmm, it doesn't seem to be a standard image (or getimagesize didn't
		//work for some reason) we'll have to pull out the big guns!
		if (class_exists('finfo',false)) {
			//try the PECL finfo approach, this would be the preffered choice
			//if only it were available on all servers...  It will be in PHP 5.3
			//though!  whoohoo!
			
			$file_info = new finfo(FILEINFO_MIME);
			//Note, we do NOT use $this->file_get_contents() as that forces file
			//to be in jail, and that is not always the case for this method.
			$mime_type = $file_info->buffer(file_get_contents($file));  // e.g. gives "image/jpeg"
			if ($mime_type && substr($mime_type, 0, 24)  !== 'application/octet-stream') {
				//cool, it worked!  Push through ms doc correction
				return self::correctMSDocMagicMime($extension, $mime_type);
			}
		}
		
		//well that didn't work either!  Lets try this then, it will only work
		//in linux though, and only if system calls are allowed:
		$mime_type = exec("file -i -b $file");
		
		if ($mime_type && substr($mime_type, 0, 24)  !== 'application/octet-stream') {
			//able to find it with this method, and was not "application/octet-stream"
			//(which basically means that server didn't know what type it was)
			
			//push it through ms correction in case magic detection is off for MS
			//file types...
			return self::correctMSDocMagicMime($extension, $mime_type);
		}
		
		if ($uploadName && $extension) {
			//as a last resort, use the extension and compare to mimes in DB to see
			//what the mime type is
			
			$row = DataAccess::getInstance()->GetRow("SELECT `mime_type` FROM ".geoTables::file_types_table."
				WHERE `extension`=?", array(''.$extension));
			if (isset($row['mime_type']) && $row['mime_type'] && strpos($row['mime_type'], 'image/') !== 0) {
				return $row['mime_type'];
			}
		}
		
		if ($defaultMime && strpos($defaultMime, 'image/') !== 0 && $defaultMime !== 'application/octet-stream') {
			//as a last last result, and only if defaultMime is passed in and 
			//not for image type and not octet-stream, return defaultMime
			return $defaultMime;
		}
		
		//wow, none of those worked?
		return false;
	}
	
	/**
	 * Given the "detected" mime-type when magic detection is used (figuring out mime
	 * by looking at file contents), and the file's extension, it figures out
	 * if the detected mime-type is wrong due to it being MS zipped document,
	 * which indicates the detected mime-type should be ignored and the next "detection"
	 * should be attempted, or is detected as application/msword when really it
	 * is excel or powerpoint...
	 * 
	 * This is a static method since it only takes the extension and detected
	 * mime-type into account, there are no "in jail" checks to make for this.
	 * 
	 * This is used internally by getMimeType() but can be used externally if needed.
	 * 
	 * @param string $extension The file's extension, not including beginning '.'
	 * @param string $mime_type The detected mime type for the file.
	 * @return string The corrected mime type if correction is needed, or 
	 *   original mime type if no correction is warranted.
	 * @since Version 6.0.0
	 */
	public static function correctMSDocMagicMime ($extension, $mime_type)
	{
		$overload = geoAddon::triggerDisplay('overload_geoFile_correctMSDocMagicMime', compact('extension', 'mime_type'), geoAddon::OVERLOAD);
		if ($overload !== geoAddon::NO_OVERLOAD) {
			return $overload;
		}
		
		//clean inputs
		$extension = ltrim($extension,'.');
		$mime_type = $mime_type_check = trim($mime_type);
		
		//get rid of anything after ; in mime-type check
		if (strpos($mime_type_check,';')!==false) {
			$mime_type_check = trim(substr($mime_type, 0, strpos($mime_type, ';')));
		}
		
		if ($mime_type_check == 'application/msword' && $extension != 'doc') {
			//was detected as application/msword but extension was not doc... figure out real mime type
			if ($extension == 'xls') {
				//ms excel file type
				return 'application/vnd.ms-excel';
			}
			if ($extension == 'ppt') {
				//ms powerpoint file type
				return 'application/vnd.ms-powerpoint';
			}
		}
		
		//list of different zip mimetypes
		$zip_types = array (
			'application/x-zip',
			'application/zip',
		);
		//we ONLY use known MS doc types, do not use regex to just blindly let
		//through any ***x or ***m as a possible MS doc.
		$ms_2k7_extensions = array (
			//Word
			'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',//Document
			'docm'=>'application/vnd.ms-word.document.macroEnabled.12',//Macro-enabled document
			'dotx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.template',//Template
			'dotm'=>'application/vnd.ms-word.template.macroEnabled.12',//Macro-enabled template
			//Excel
			'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',//Workbook
			'xlsm'=>'application/vnd.ms-excel.sheet.macroEnabled.12',//Macro-enabled workbook
			'xltx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.template',//Template
			'xltm'=>'application/vnd.ms-excel.template.macroEnabled.12',//Macro-enabled template
			'xlsb'=>'application/vnd.ms-excel.sheet.binary.macroEnabled.12',//Non-XML binary workbook
			'xlam'=>'application/vnd.ms-excel.addin.macroEnabled.12',//Macro-enabled add-in 
			//PowerPoint
			'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',//Presentation
			'pptm'=>'application/vnd.ms-powerpoint.presentation.macroEnabled.12',//Macro-enabled presentation
			'potx'=>'application/vnd.openxmlformats-officedocument.presentationml.template',//Template
			'potm'=>'application/vnd.ms-powerpoint.presentation.macroEnabled.12',//Macro-enabled template
			'ppam'=>'application/vnd.ms-powerpoint.addin.macroEnabled.12',//Macro-enabled add-in
			'ppsx'=>'application/vnd.openxmlformats-officedocument.presentationml.slideshow',//Show
			'ppsm'=>'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',//Macro-enabled show
			'sldx'=>'application/vnd.openxmlformats-officedocument.presentationml.slide',//Slide
			'sldm'=>'application/vnd.ms-powerpoint.slide.macroEnabled.12',//Macro-enabled slide 
			'thmx'=>'application/vnd.ms-officetheme',//Office theme
		);
		
		if (!$extension || !$mime_type_check) {
			//can't do detection without mime type and extension!
			return $mime_type;
		}
		
		if (isset($ms_2k7_extensions[$extension]) && in_array($mime_type_check, $zip_types)) {
			//this was detected as zip and the extension is one of the MS zipped doc types
			return $ms_2k7_extensions[$extension];
		}
		//does not fall into any of the MS file types that need correcting
		return $mime_type;
	}
	
	/**
	 * Quick method to determine if it is windows environment or not
	 * @return boolean
	 * @since Version 7.1.0
	 */
	public static function isWindows ()
	{
		return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
	}
	
	/**
	 * Gets the latest error produced, or empty string if no errors were produced yet.
	 * @return string
	 */
	public function errorMsg ()
	{
		return $this->_error;
	}
	
	/**
	 * Simple method to get the "pretty" file size.
	 * 
	 * @param int $size The file size to show, in bytes
	 * @param int $decimals Number of decimal places to round to, default 2
	 * @return string The pretty file size, something like 1.23 KB
	 * @since Version 7.3.0
	 */
	public static function prettySize ($size, $decimals = 2)
	{
		$a = array("B", "KB", "MB", "GB", "TB", "PB");
		
		$pos = 0;
		$size = (int)$size;
		$decimals = (int)$decimals;
		while ($size >= 1024) {
			$size /= 1024;
			$pos++;
		}
		return round($size,$decimals)." ".$a[$pos];
	}
	
	/**
	 * Used internally
	 * @internal
	 */
	private $_error='';
	/**
	 * Used internally to add an admin error.
	 * 
	 * @param string $msg
	 */
	private function _adminError ($msg)
	{
		if (defined('IN_ADMIN')) {
			geoAdmin::m($msg, geoAdmin::ERROR);
		}
		$this->_error = $msg;
	}
}