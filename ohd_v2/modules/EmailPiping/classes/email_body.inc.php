<?php
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Email message decoder/container.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

//require_once 'Mail/mimeDecode.php';

class EmailBody
{
	var $text_body;
	var $attaches;

	function EmailBody($raw_message = '')
	{
		 $this->process_raw_message($raw_message);
	}

	function process_raw_message($raw_message)
	{
		 $this->text_body = array();
		 $this->attaches  = array();
		 
		 if (empty($raw_message)) {
			  return;
		 }
		 $parts = $this->get_message_parts($raw_message);

		 $this->kill_twins($parts);
		 foreach ($parts as $part) {
			  $this->process_one_part($part);
		 }
	}

	function get_attaches()
	{
		 return $this->attaches;
	}

	function get_text_message()
	{
		 return implode('<hr>', $this->text_body);
	}

	function get_message_parts($whole_message)
	{
		/*echo Mail_mimeDecode::_decodeHeader("=?iso-8859-1?Q?Exposici=F3n_del_FARO");
		die(); */
		
		$parts = array();
		$this->mime_get_all_parts(
			Mail_mimeDecode::decode(
				array (
					'input'          => $whole_message,
					'include_bodies' => true, 
					'decode_bodies'  => true,
					'decode_headers' => false
				)
			), 
			$parts
		);
		
		return $parts;
	}


	function mime_get_all_parts($structure, &$result)
	{
		if (isset($structure->body))
		{
			$result[] = (array)$structure;
			return;
		}

		if (isset($structure->parts)) {
			foreach ($structure->parts as $part) {
				$this->mime_get_all_parts($part, $result);
			}
			return;
		}
	}


	function kill_twins(&$parts)
	{
		if (count($parts) < 2) {
			return;
		}
		
		$cond1 = $parts[0]['ctype_primary'] == 'text' AND $parts[1]['ctype_primary'] == 'text';
		$cond2 = $parts[0]['ctype_secondary'] == 'plain' AND $parts[1]['ctype_secondary'] == 'html';
		$str1 = trim($parts[0]['body']);
		$str2 = trim(strip_tags($parts[1]['body']));
		$f=0;
		$cond3 = similar_text($str1,$str2 , $f);
		if ($cond1 && $cond2 and $f > 'EC_PERCENTS') {
			unset($parts[0]);
		}
	}

	function clear_html_tags($contents)
	{
		if (preg_match_all("~<body.*>(.*)</body>~Usi", $contents, $M)) {
			return trim(implode(',', $M[1]));
		}
		return $contents;
	}


	function format_text_message($type, $contents)
	{
		if ('plain' == $type) {
			return nl2br(trim($contents));
		}
		return $this->clear_html_tags($contents);
	}


	function get_attach_properties($part_info)
	{
		 $result = array(
		   'body'      => $part_info['body'],
		   'filename'  => ''
		 );
		 
		 if (isset($part_info['d_parameters']['filename'])) {
			  $result['filename'] = $part_info['d_parameters']['filename'];
		 }
		 elseif(isset($part_info['ctype_parameters']['name'])) {
			  $result['filename'] = $part_info['ctype_parameters']['name'];
		 }
		 return $result;
	}


	function process_one_part($part)
	{
		if ('text' == $part['ctype_primary']) {
			$this->text_body[] = $this->format_text_message($part['ctype_secondary'], $part['body']);
		}
		 
		if (isset($part['disposition'])) {
			$this->attaches[] = $this->get_attach_properties($part);
		}
	}

}
?>