<?php

/**
 * Converts valid HTML code to BB-code. 
 * All unknown html-tag strips
 *
 * @param    string  $html   html
 * @param    string  $tags   set of allowed tags
 * @return   string  valid bb-code text
 *
 * @author   Konstantin Gorbachov <slyder@bk.ru>
 * @version  1.0.0
 */
function html2bb($html, $tags = '<a><b><i><u>')
{
	/*
	echo "<pre><xmp>";
	var_dump($html);/**/
	$html = strip_tags($html, $tags);

	$search = array (
				"'<script[^>]*?>.*?</script>'si",  // Strip out javascript
				"<br>",
				"</p>",
				"'([\r\n])[\s]+'",                 // Strip out white space
				"'&(quot|#34);'i",                 // Replace html entities
				"'&(amp|#38);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i",
				"'&(nbsp|#160);'i",
				"'&(iexcl|#161);'i",
				"'&(cent|#162);'i",
				"'&(pound|#163);'i",
				"'&(copy|#169);'i",
				// html -> bb
				"'<b>'i", "'</b>'i",
				"'<i>'i", "'</i>'i",
				"'<a>'i", "'</a>'i", 
				"'<a.*?href=\"(.+?)\".*?>'i", "'</a>'i", //[url=http://www.server.org]server[/url]
					 
	);                   

	$replace = array (
				"",
				"\r\n",
				"\r\n",
				"\\1",
				"\"",
				"&",
				"<",
				">",
				" ",
				chr(161),
				chr(162),
				chr(163),
				chr(169),
				// html -> bb,
				"[b]", "[/b]",
				"[i]", "[/i]",
				"[url]", "[/url]",
				"[url=$1]", "[/url]"
	);

	$html = preg_replace ($search, $replace, $html);

	/*	echo "\n";
	var_dump($html); /**/
	return $html;
}

?>
