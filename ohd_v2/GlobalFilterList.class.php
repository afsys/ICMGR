<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Includes and initialise filter classes 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */


require_once(FILTER_DIR . 'SmartyFilter.class.php');
require_once(FILTER_DIR . 'sxDBFilter.class.php');
require_once(FILTER_DIR . 'FCKeditorFilter.class.php');

class GlobalFilterList extends FilterList
{

	/**
	 * Create a new GlobalFilterList instance.
	 *
	 * @access public
	 * @since  1.0
	 */
	function & GlobalFilterList ()
	{
		parent::FilterList();

		$this->filters['SmartyFilter']        =& new SmartyFilter();
		$this->filters['sxDBFilter']          =& new sxDBFilter();
		$this->filters['FCKEditor']           =& new FCKeditorFilter();
	}

}

?>