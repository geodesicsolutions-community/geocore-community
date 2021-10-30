<?php
//Pagination.class.php
/**
 * Holds the geoPagination class.  For displaying page number links and stuff.
 * 
 * @package System
 * @since Version 4.0.0
 */



/**
 * Utility functions useful for generating pagination of a results page
 * such as when browing ads or searching
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoPagination {
				
	/**
	 * Gets the pagination in an HTML string
	 *
	 * @param int $totalPages The total number of pages of results
	 * @param int $currentPage Page the user is currently viewing
	 * @param String $link Base URL of all pagination links
	 * @param String $css CSS class to apply to all pagination items
	 * @param String $postLink string appended to the end of each pagination URL. Useful if the page number needs to go somewhere in the middle
	 * @param bool $showAll If true, will include an option "all" that will add
	 *   a page with value "all".  The page value of all would need to be special
	 *   coded so that value of all removes the limits and shows all results.
	 * @param bool $addRelHints If true, adds meta rel=prev/next links to the page head (to help search engines find related pages)
	 * @param String $moduleTag only set if paginating a module, to hold the name of that module's tag. If set, adds some JS to make the links all ajaxy and stuff
	 * @return String pagination links, in HTML
	 */
	public static function getHTML($totalPages, $currentPage=1, $link, $css='', $postLink='', $showAll=false, $addRelHints=true, $moduleTag='')
	{
		$tpl = new geoTemplate('system', 'classes');
		$tpl->assign('url', $link);
		$tpl->assign('postUrl', $postLink);
		$tpl->assign('css', $css);
		$tpl->assign('currentPage', $currentPage);
		
		$tpl->assign('previousPage', ($currentPage != 1) ? ($currentPage-1) : false);
		$tpl->assign('nextPage', ($currentPage != $totalPages) ? ($currentPage+1) : false);
		
		$rangeStart = ($showAll && $currentPage==='all')? 1 : $currentPage;
		
		$ranges['small']['low'] = $rangeStart - 3;
		$ranges['small']['high'] = $rangeStart + 3;
		$ranges['middle']['low'] = $rangeStart - 30;
		$ranges['middle']['high'] = $rangeStart + 30;
		$ranges['high']['low'] = $rangeStart - 300;
		$ranges['high']['high'] = $rangeStart + 300;
		
		$links = array();
		// always show a link to the first page, current page, and last page
		$links[] = 1;
		if (!$showAll || $currentPage!=='all') {
			//don't add current page if it is all, that will always get added at
			//end of this
			self::addIfUnique($currentPage, $links);
		}
		self::addIfUnique($totalPages, $links);
		
		for($i = 2; $i <= $totalPages; $i++) {
			if ($i % 100 == 0 && $i >= $ranges['high']['low'] && $i <= $ranges['high']['high']) {
				self::addIfUnique($i, $links);
			} elseif($i % 10 == 0 && $i >= $ranges['middle']['low'] && $i <= $ranges['middle']['high']) {
				self::addIfUnique($i, $links);
			} elseif ($i >= $ranges['small']['low'] && $i <= $ranges['small']['high']) {
				self::addIfUnique($i, $links);
			}
		}
		
		sort($links, SORT_NUMERIC);
		
		if ($showAll) {
			self::addIfUnique('all', $links);
			if ($currentPage==='all') {
				//fix previous/next links since they do not apply
				$tpl->assign('previousPage', false);
				$tpl->assign('nextPage',false);
			}
		}
		
		$tpl->assign('links', $links);
		
		//add rel hints for Google
		//more info: http://support.google.com/webmasters/bin/answer.py?hl=en&answer=1663744
		if($currentPage !== 'all' && $addRelHints) {
			$view = geoView::getInstance();
			if($currentPage != 1) {
				$previous = $link . ($currentPage-1) . $postLink;
				$view->addTop('<link rel="prev" href="'.$previous.'" />');
			}
			if($currentPage != $totalPages) {
				$next = $link . ($currentPage+1) . $postLink;
				$view->addTop('<link rel="next" href="'.$next.'" />');
			}
		}
		if(defined('IN_ADMIN')) {
			$tpl->assign('skip_glyphs',1);
		}
		
		$tpl->assign('moduleTag', $moduleTag);
		
		return $tpl->fetch('Pagination/pagination.tpl');
	}
	
	/**
	 * Adds a number to an array only if it doesn't already exist in the array
	 *
	 * @param int $add the number to add
	 * @param array $array the original array
	 * @return array the modified array
	 */
	private static function addIfUnique($add, &$array)
	{
		if(!in_array($add, $array)) {
			$array[] = $add;
		}
	}
}