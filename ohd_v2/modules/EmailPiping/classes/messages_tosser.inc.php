<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Class for processing filters.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */


class MessagesTosser
{
	var $FilteringRules;
	var $default_result;

	function MessagesTosser($FilteringRules, $default_result)
	{
		$this->set_filtering_rules($FilteringRules);
		$this->default_result = null;
		//$default_result;
	}

	function set_filtering_rules($FilteringRules)
	{
		$this->FilteringRules = $FilteringRules;
	}

	function get_words($string)
	{
		$result = array();
		if (!preg_match_all("~([\w\d']+)|@~", strtolower($string), $M)) {
			return $result;
		}
		return $M[0];
	}

	function words_matched($string_rule, $string_message)
	{
		$rule_words    = $this->get_words($string_rule);
		$message_words = $this->get_words($string_message);
		if (empty($rule_words)) {
			return true;
		}

		return count($rule_words) == count(array_intersect($rule_words, $message_words));
	}

	function words_not_matched($string_rule, $string_message)
	{
		$rule_words    = $this->get_words($string_rule);
		$message_words = $this->get_words($string_message);
		if (empty($rule_words)) {
			return true;
		}
		return !count(array_intersect($rule_words, $message_words));
	}
	
	function get_email($from)
	{
		if (!preg_match("~<([^>]+)>~", $from, $M)) {
			return trim($from);
		}
		return $M[1];
	}	

	function is_rule_matched($Rule, $EmailData)
	{
		$result = true;
		$result = $result && $this->words_matched($this->get_email($Rule['addr_from']),    $this->get_email($EmailData['from']));
		$result = $result && $this->words_matched($this->get_email($Rule['addr_to']),      $this->get_email($EmailData['to']));
		$result = $result && $this->words_matched($Rule['subject'],      $EmailData['subject']);
		$result = $result && $this->words_matched($Rule['words'],        $EmailData['body']);
		$result = $result && $this->words_not_matched($Rule['no_words'], $EmailData['body']);

		return $result;
	}


	function determine_rule_id($EmailData)
	{
		foreach ($this->FilteringRules as $Rule) {
			if ($this->is_rule_matched($Rule, $EmailData)) {
				return $Rule;
			}
		}

		return $this->default_result;
	}

}
?>