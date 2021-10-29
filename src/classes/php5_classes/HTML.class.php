<?php
//HTML.class.php
/**
 * Holds the utility class geoHTML.
 * 
 * @package System
 * @since Version 4.0.0
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
## ##    16.09.0-106-ge989d1f
## 
##################################

/**
 * Class to generate chunks of HTML that are very common, used the most in the
 * admin panel.
 * 
 * @package System
 * @since 4.0.0
 */
class geoHTML{

	/**
	 * Used by get row color function, to allow alternating colors.
	 *
	 * @var int 1 or 2
	 */
	private static $_getRowColorCurrentColor;
	
	
	
	/**
	 * Generate a numeric drop down
	 *
	 * @param unknown_type $id
	 * @param unknown_type $start
	 * @param unknown_type $len
	 * @param unknown_type $selected
	 * @param unknown_type $style
	 * @return unknown
	 */
	public static function getNumericDropDown($id='numeric',$start=0,$len=100,$selected=0,$style='')
	{
		$tpl = new geoTemplate('admin');
		$tpl->assign('style', $style);
		$tpl->assign('id', $id);
		$options = array();
		for($i=$start;$i <= $len;$i++){
			$options[$i]['value'] = $i;
			$options[$i]['label'] = $i;
			if($i == $selected) {
				$options[$i]['selected'] = true;
			}
		}
		$tpl->assign('options', $options);
		
		return $tpl->fetch('HTML/dropdown.tpl');
	}
	
	/**
	 * Generare an html element
	 *
	 * @param string $type
	 * @param string $id_name
	 * @param string $default_value
	 */
	public static function input($type,$id_name,$default_value='')
	{
		$tpl = new geoTemplate('admin');
		$tpl->assign('type', $type);
		$tpl->assign('id', $id_name);
		$tpl->assign('default_value', $default_value);
		return $tpl->fetch('HTML/input.tpl');
	}
	
	/**
	 * Generate a dropdown within an array
	 *
	 * @param unknown_type $id
	 * @param unknown_type $input
	 * @param unknown_type $selected
	 * @param unknown_type $style
	 * @return unknown
	 */
	public static function getArrayDropDown($id='dropdown',$input=array(),$selected=0,$style='')
	{
		$tpl = new geoTemplate('admin');
		$tpl->assign('style', $style);
		$tpl->assign('id', $id);
		$tpl->assign('selected', $selected);
		$tpl->assign('options', $input);
		
		return $tpl->fetch('HTML/dropdown.tpl');
	}

	/**
	 * Reset automatic color switchers to starting color, for use between groups of admin switches.
	 *
	 */
	public static function resetRowColor()
	{
		self::$_getRowColorCurrentColor = 1;
	}
	
	/**
	 * Gets the class name for a row color to be used in the admin in an HTML element.  Automatically alternates between
	 * row_color1 and row_color2.
	 *
	 * @param int $default If you want to change it so that the first row is row_color2, set this to 2
	 * @param boolean $change_color if set to false, the row class will not be alternated before returning it.
	 * @return string
	 */
	public static function adminGetRowColor($default = 1,$change_color = true)
	{
		self::$_getRowColorCurrentColor = (!isset(self::$_getRowColorCurrentColor))?  $default : self::$_getRowColorCurrentColor;
		if ($change_color){
			self::$_getRowColorCurrentColor=(self::$_getRowColorCurrentColor == 1)? 2:1;
		}
		$return =  'row_color'.self::$_getRowColorCurrentColor;
		
		if(strpos($change_color,'#') !==false) {
			$return .= " style='background-color:$change_color";
		}
		return $return;
	}
	
	/**
	 * Wraps some HTML in a fieldset, handy if you are feeling particularly lazy.
	 * 
	 * @param string $html
	 * @param string $legend
	 * @param string $id
	 * @return string
	 */
	public static function fieldset($html='',$legend='',$id='')
	{
		$tpl = new geoTemplate('admin');
		$tpl->assign('legend', $legend);
		$tpl->assign('html', $html);
		$tpl->assign('id', $id);
		return $tpl->fetch('HTML/fieldset.tpl');
	}
	
	/**
	 * Gives you a line looking thingy similar to an <hr> but using a div
	 * because I guess we're too good for <hr>'s.
	 * @return string
	 */
	public static function separator()
	{
		$tpl = new geoTemplate('admin');
		return $tpl->fetch('HTML/separator.tpl');
	}
	
	/**
	 * OK by header, the original author aparently meant heading.  Just pass
	 * in stuff and it will get put into a heading, I think...
	 * 
	 * @return string Some HTML.
	 */
	public static function addHeader()
	{
		$args = func_get_args();
		$tpl = new geoTemplate();
		
		foreach($args as $k =>$v) {
			if(is_array($v)) {
				
			}
		}
		$tpl->assign('header',$args);
		$tpl->assign('total',count(array_keys($args)));
		$tpl->assign('line_limit',count(array_keys($args)) -1);
		return $tpl->fetch('HTML/add_header.tpl');
	}
	
	/**
	 * Not really a header, but a heading (we may be re-naming this soon).  Pass
	 * in some stuff to put in a 2 column heading.
	 * @return string The HTML to use to display said heading.
	 */
	public static function addTwoColumnHeader()
	{
		$args = func_get_args();
		$tpl = new geoTemplate();
		
		foreach($args as $k =>$v) {
			if(is_array($v)) {
				
			}
		}
		$tpl->assign('header',$args);
		$tpl->assign('total',count(array_keys($args)));
		$tpl->assign('line_limit',count(array_keys($args)) -1);
		return $tpl->fetch('HTML/add_header.tpl');
	}
	
	/**
	 * Add a 3 column heading, this may be removed soon.
	 * 
	 * @param string $col1
	 * @param string $col2
	 * @param string $col3
	 * @return string
	 */
	public static function addThreeColumnHeader($col1=null,$col2=null,$col3=null)
	{
		$tpl = new geoTemplate();
		
		$tpl->assign('col1',$col1);
		$tpl->assign('col2',$col2);
		$tpl->assign('col3',$col3);
	
		$color = self::adminGetRowColor(true,1);
		$tpl->assign('color', $color);
		return $tpl->fetch('HTML/add_three_column_header.tpl');
	}
	
	/**
	 * I don't even know what this does.  Something to do with columns and rows
	 * and the number 3.
	 * 
	 * @param $col1
	 * @param $col2
	 * @param $col3
	 * @param $id
	 * @return string
	 */
	public static function addThreeColumnRow($col1=null,$col2=null,$col3=null,$id=null)
	{
		$tpl = new geoTemplate();
		
		$tpl->assign('col1',$col1);
		$tpl->assign('col2',$col2);
		$tpl->assign('col3',$col3);
		$tpl->assign('id',$id);
		$color = self::adminGetRowColor(true,1);
		$tpl->assign('color', $color);
		return $tpl->fetch('HTML/add_three_column_row.tpl');
	}
	
	/**
	 * For use in the admin mostly, to easily add a 2 column setting/value that uses the leftColumn rightColumn classes,
	 * and alternates the colors for you.
	 *
	 * @param string $option the right (or only) column/setting name
	 * @param string $html_value The left column/setting value.  If not specified, the $option will fill the entire width.
	 * @param string $eg Displayed below the option but smaller.
	 * @param boolean $change_color_or_class if false, won't alternate the row color before setting it.
	 * @param string $left_html Don't use this, it's stupid
	 * @param string $right_html Don't use this one either!
	 * @param string $id Probably the id for the option
	 * @return string All the html needed to add the setting/value to the page
	 * @deprecated Feb 19, 2013 7.1.1 - don't use this
	 */
	public static function addOption($option, $html_value=null,$eg =null,$change_color_or_class = true,$left_html='',$right_html='',$id='')
	{
		$tpl = new geoTemplate('admin');
		
		if ($eg  !==null){
			$tpl->assign('eg', $eg);			
		}
		if (strlen(trim($id))> 0){
			$tpl->assign('id',$id);
		}
		$tpl->assign('left_html', $left_html);
		$tpl->assign('right_html', $right_html);
		
		if(strpos($change_color_or_class,"#")!==false) {
			$tpl->assign('color_assist', $change_color_or_class ." !important");
		}else if(strpos($change_color_or_class,".")!==false) {
			$tpl->assign('class_assist', str_replace(".","",$change_color_or_class));
		} 
		$tpl->assign('color',self::adminGetRowColor(1, $change_color_or_class));
		$tpl->assign('option',$option);
		if ($html_value !== null){
			$tpl->assign('html_value',$html_value);
		}
		return $tpl->fetch('HTML/add_option.tpl');
	}
	
	/**
	 * Adds a simple button that links to somewhere.  Use this so that all button type stuff looks
	 * uniform in the software.
	 * 
	 * If you need something more fancy, like an ajax button or something, look at the (not created yet)
	 * class geoButton.
	 *
	 * @param string $label The text in the button
	 * @param string $link Where the button links to.
	 * @param bool $link_is_really_javascript
	 * @param string $id
	 * @param string $class
	 * @return string
	 */
	public static function addButton($label, $link, $link_is_really_javascript = false,$id='',$class='mini_button'){
		$tpl = new geoTemplate('admin');
		
		$tpl->assign('label',$label);
		$tpl->assign('link',$link);
		$tpl->assign('link_is_really_javascript',$link_is_really_javascript);
		$tpl->assign('id',$id);
		$tpl->assign('class',$class);
				
		return $tpl->fetch('HTML/add_button.tpl');
	}
	
	/**
	 * Generates a save button for admin pages
	 *
	 * @param string $url
	 * @param string $id
	 * @return string
	 */
	public static function addSaveButton($url=null,$id=null)
	{
		return self::addOption(geoHTML::addButton('Save',$url,(($url)?true:false),$id,(($url)?null:'submit')),null,null,"#ffffff");
	}
	
	/**
	 * Displays the little question mark icon with the tooltip specified elsewhere.
	 *
	 * @param string $title The title of the tooltip
	 * @param string $text The text to display, HTML allowed
	 * @return string HTML to put in your page to display the tooltip.
	 */
	public static function showTooltip($title, $text) {
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		$tpl->assign('title', $title);
		$tpl->assign('text', $text);
		return $tpl->fetch('HTML/tooltip.tpl');
	}

	/**
	 * SMARTY version of the multi-dropdown date selects
	 *
	 * @param array $fields "name" attributes of the select fields. Required indecies: year, month, day, hour, minute
	 * @param array $labels text labels for each dropdown. optional. Required indecies: year, month, day, hour, minute
	 * @param int $timestamp ticktime to set all dropdowns. overrides individual settings in $values param.
	 * @param array $values starting values of the select fields. Required indecies: year, month, day, hour, minute. Only used if $timestamp = 0
	 * @param bool $isPlacementEndTime special-case used to assign ID parameter to endtime fields during listing placement
	 * @return String HTML select dropdowns from template
	 */
	public static function dateSelect($fields, $labels = array(), $timestamp=0, $values=array(), $isPlacementEndTime=false)
	{
		$tpl = new geoTemplate('system','classes');
		$timestamp = intval($timestamp);
		
		$show_time = array();
		$now = geoUtil::time();
		if ($timestamp == 0) {
			$show_time['year'] = (is_numeric($values['year']) && $values['year'] > 0) ? $values['year'] : date("Y",$now);
			$show_time['month'] = (is_numeric($values['month']) && $values['month'] > 0) ? $values['month'] : date("n",$now);
			$show_time['day'] = (is_numeric($values['day']) && $values['day'] > 0) ? $values['day'] : date("j",$now);
			$show_time['hour'] = (is_numeric($values['hour']) && $values['hour'] > 0) ? $values['hour'] : date("G",$now);
			$show_time['minute'] = (is_numeric($values['minute']) && $values['minute'] > 0) ? $values['minute'] : date("i",$now);
		} else {
			$show_time['year'] = date("Y",$timestamp);
			$show_time['month'] = date("n",$timestamp);
			$show_time['day'] = date("j",$timestamp);
			$show_time['hour'] = date("G",$timestamp);
			$show_time['minute'] = date("i",$timestamp);
		}
		
		if(!count($labels)) {
			$labels['year'] = 'year';
			$labels['month'] = 'month';
			$labels['day'] = 'day';
			$labels['hour'] = 'hour';
			$labels['minute'] = 'minute';
		}
		
		$currentYear = date("Y",$now);
		if(defined('IN_ADMIN')) {
			//this is the Admin using this functionality to change dates around -- give him a wider range of years
			$years = array();
			for($i=$currentYear; $i <= $show_time['year']+2; $i++) {
				$years[] = $i;
			}
			//admin styles things different now...
			$tpl->assign('in_admin',true);
		} else {
			//on the front side, probably placing/editing a listing's start/end times -- show only the current year and the next two
			$years = array($currentYear, $currentYear+1, $currentYear+2);
		}
		
		$tpl->assign('years', $years);
		
		$tpl->assign('names', $fields);
		$tpl->assign('values', $show_time);
		$tpl->assign('isEnd', $isPlacementEndTime);
		$tpl->assign('labels', $labels);
		
		$html = $tpl->fetch('HTML/date_select.tpl');
				
		return $html;

	}
}
