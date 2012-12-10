<?php
//
// +------------------------------------------------------------------------+
// | SX common modules                                                      |
// +------------------------------------------------------------------------+
// | Copyright (c) 2004 Konstantin Gorbachov                                |
// | Email         slyder@bk.ru                                             |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//

/**
 * HTML Email cleaner module for PHP 4.2.0
 *
 * Class offer different operation for dump functions.
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Dev
 */
class sxEmailsHtmlCleaner
{
	/**
	 * Array of fixes for distored items.
	 * @var array
	 */
	var $cases = array();
	var $source_html = null;
	var $clear_html = null;
	
	
	/**
	 * Constructor
	 */    
	function sxEmailsHtmlCleaner($source_html)
	{
		$this->setSourceHtml($source_html);
	}    
	
	/**
	 * __
	 * @param     string    $ip    __
	 */    
	function addCase($case)
	{

	}
	
	function clearHtml()
	{
		$text = $this->source_html;
		
		
		// test item
		// $text = preg_replace('/(a)/', '<span style="color: red;">\1</span>', $text);
		
		
		
		// break long sequences
		// like: nstanceName=prefs[emails_templates][ticket_new_message]&toolbar=Accessibility]http://helpdesk.passolo.com/lib/FCKEditor/editor/fckeditor.html?InstanceName=prefs[emails_templates][ticket_new_message]&toolbar=Accessibility
		$text = preg_replace('/([\w:\/\.\[\]&=\?]{120})/', '\1<br />', $text);
		
		
		$this->clear_html = $text;
	}
	
	function getClearHtml($source_html)
	{
		return $this->clear_html;
	}    

	function getSourceHtml()
	{
		return $this->source_html;
	}    
	
	function setSourceHtml($source_html)
	{
		$this->source_html = $source_html;
	}
	
}
