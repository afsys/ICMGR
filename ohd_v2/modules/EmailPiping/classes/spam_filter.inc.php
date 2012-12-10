<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Class for matching tickets to spam filter.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jun 11, 2006
 * @version    1.00 Beta
 */

class SpamFilter
{
	var $caption_filter = null;
	var $body_filter    = null;
	
	
	function SetVocabubaries($caption_filter, $body_filter)
	{
		$this->caption_filter = $caption_filter;
		$this->body_filter    = $body_filter;
	}
	
	function Match($email_obj)
	{
		if (!empty($this->caption_filter)) {
			$patterns = explode("\n", $this->caption_filter);
			if ($this->MatchPatterns($patterns, $email_obj['subject'])) return true;
		}
		
		if (!empty($this->body_filter)) {
			$patterns = explode("\n", $this->body_filter);
			if ($this->MatchPatterns($patterns, $email_obj['body'])) return true;
		}
		
		return false;
	}
	
	function MatchPatterns($patterns, $text) 
	{
		// var_dump($text);
		
		foreach ($patterns as $pattern) {
			if (strpos(strtolower($text), strtolower(trim($pattern))) !== false) return true;
		}
		
		return false;
	}
}