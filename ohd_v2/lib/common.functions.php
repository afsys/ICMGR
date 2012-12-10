<?php

include 'prepare_paging_array.function.php';

function __($str)
{
	// GET TRANSLATION
	global $lang;
	static $is_loaded  = false;
	static $on_session = false;
	
	if (false === $is_loaded || (!empty($_SESSION) && !$on_session)) {
		$on_session = !empty($_SESSION);
		$is_loaded = true;
		
		global $lang_params;
		$data = include 'Langs/index.php';
		$lang        = $data['lang'];
		$lang_params = $data['lang_params'];
	}
	if ($str == '') return;

	if (!empty($lang[$str])) return $lang[$str];
	return $str; /**/
	
	if (!empty($lang[$str])) return " - ".$lang[$str]." - ";
	return " - ".$str." - ";
}

function getLangsList()
{
	$ldir = OHD_LIB_DIR.'Langs/';
	$larr = array();
	
	$d = dir($ldir);
	while (false !== ($entry = $d->read())) {
	    if ($entry == '.' || $entry == '..' || $entry == '.svn') continue;
	    
	    if (file_exists($ldir.'/'.$entry.'/index.php'))
	    {
	    	$a = include($ldir.'/'.$entry.'/index.php');
	    	if (is_array($a)) $larr[] = $a;
	    }
	}
	$d->close();
	return $larr;
}


	
/**
 * Suppose what given txt is html
 * @param     string   $text      some text
 * @return    boolean  true if possible html else false
 */
function is_html($text)
{
	if (preg_match('/((<br(?:\s*\/)?>|<div>|<b>)[^<>]+?){2}/i', $text))
	{
		return true;
	}
	return false;
}
	
	
	
	
if (!function_exists("http_build_query"))
{

	function http_build_query($formdata, $numeric_prefix = null) {
		// If $formdata is an object, convert it to an array
		if (is_object($formdata)) {
			$formdata = get_object_vars($formdata);
		}

		// Check we have an array to work with
		if (!is_array($formdata)) {
			trigger_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.', E_USER_WARNING);
			return false;
		}

		// If the array is empty, return null
		if (empty($formdata)) {
			return;
		}

		// Argument seperator
		$separator = "&";

		// Start building the query
		$tmp = array ();
		foreach ($formdata as $key => $val) {
			if (is_integer($key) && $numeric_prefix != null) {
				$key = $numeric_prefix . $key;
			}

			if (is_scalar($val)) {
				array_push($tmp, urlencode($key).'='.urlencode($val));
				continue;
			}

			// If the value is an array, recursively parse it
			if (is_array($val)) {
				array_push($tmp, __http_build_query($val, urlencode($key)));
				continue;
			}
		}

		return implode($separator, $tmp);
	}

	// Helper function
	function __http_build_query ($array, $name)
	{
		$tmp = array ();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				array_push($tmp, __http_build_query($value, sprintf('%s[%s]', $name, $key)));
			} elseif (is_scalar($value)) {
				array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
			} elseif (is_object($value)) {
				array_push($tmp, __http_build_query(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
			}
		}

		// Argument seperator
		$separator = "&";

		return implode($separator, $tmp);
	}


}



// WORDWRAP

// Maximum length of word in posting (if larger - replaced
// with <span title="..">...</span>).
define("DK_MAX_WORD_LEN", 50);
define("DK_MAX_WORD_LEN_FRACTION", 0.7);

// Version 1.03.
// Replace long_lines_outside_tags with
// <span title="long_lines_outside_tags">long_line...e_tags</span>.
// ATTENTION: this function is used for ALREADY QUOTED HTML code,
// e.g. all non-tag "<" must be replaced with &lt; etc.
function dk_bbcode_shrink_long($s)
{
  $num = floor(DK_MAX_WORD_LEN * 1.2);
  // PCRE cannot work with (?:a|b){X,Y} optimal - try "zzzz..." 2000
  // times to crash the function! Do not add multiplicity qualifiers
  // (like *, +, {} etc.) after LARGE (?:a|b)'s.
  return preg_replace_callback(
    '{
      ((?> # disable back-tracking - improves speed
        [^-%°·()\[\]{}«»!?\s<>]   # non-break (this INCLUDES entities!)
        {'.$num.',} # entities are longer then 1 char! So {$num} is
                    # not quite correct, but we have no choise.
      ))
      (?= [^<>]* (?: < | $))
    }sxi',
    'dk_bbcode_shrink_long_callback',
    $s
  );
}

function dk_bbcode_shrink_long_callback($p)
{
  // IE word breaks: http://www.cs.tut.fi/~jkorpela/html/nobr.html
  $s = $p[1];
  $maxlen = DK_MAX_WORD_LEN;
  $fraction = DK_MAX_WORD_LEN_FRACTION;
  $lLeft = floor($maxlen * $fraction);
  $lRight = floor($maxlen * (1-$fraction));
  $char = '(?: [^&] | &\\#?\w+; )'; // character or entity.
  // It is possible that we enter this function with not enough characters
  // (including entities) in the match. Then - do nothing, string is not
  // long enough (e.g. "&amp;&amp;...&amp;").
  if (!preg_match("/^ ($char\{$lLeft}) (.*) ($char\{$lRight}) $/sxi", $s, $p))
    return $s;
  list ($left, $middle, $right) = array($p[1], $p[2], $p[3]);
  $fulllen = $maxlen * 2;
  preg_match_all("/($char\{1,$fulllen})/sx", $s, $parts, PREG_PATTERN_ORDER);
  $full = join("&#183;&#13;", $parts[1]);
  $s =
    '<span class="shrinked" title="' . str_replace('"', '&quot;', $full) . '">' .
      '<span class="shrinked-left">' . $left . '</span>' .
      '<span style="display:none">'. $middle . '</span>' .
      '<span class="shrinked-right">' . $right . '</span>' .
    '</span>';
  return $s;
}

//HTMLWRAP Copyright (c) 2004 Brian Huisman AKA GreyWyvern http://www.greywyvern.com
function htmlwrap($str, $width = 60, $break = "\n", $nobreak = "", $nobr = "pre", $utf = false) {
  $content = preg_split("/([<>])/", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
  $nobreak = explode(" ", $nobreak);
  $nobr = explode(" ", $nobr);
  $intag = false;
  $innbk = array();
  $innbr = array();
  $drain = "";
  $utf = ($utf) ? "u" : "";
  $lbrks = "/?!%)-}]\\\"':;";
  if ($break == "\r") $break = "\n";
  
  while (list(, $value) = each($content)) 
  {
    switch ($value) {
      case "<": $intag = true; break;
      case ">": $intag = false; break;
      default:
        if ($intag) {
          if ($value{0} != "/") {
            preg_match("/^(.*?)(\s|$)/$utf", $value, $t);
            if ((!count($innbk) && in_array($t[1], $nobreak)) || in_array($t[1], $innbk)) $innbk[] = $t[1];
            if ((!count($innbr) && in_array($t[1], $nobr)) || in_array($t[1], $innbr)) $innbr[] = $t[1];
          } else {
            if (in_array(substr($value, 1), $innbk)) unset($innbk[count($innbk)]);
            if (in_array(substr($value, 1), $innbr)) unset($innbr[count($innbr)]);
          }
        } else if ($value) {
          if (!count($innbr)) $value = str_replace("\n", "\r", str_replace("\r", "", $value));
          if (!count($innbk)) {
            do {
              $store = $value;
              if (preg_match("/^(.*?\s|^)(([^\s&]|&(\w{2,5}|#\d{2,4});){".$width."})(?!(".preg_quote($break, "/")."|\s))(.*)$/s$utf", $value, $match)) {
                for ($x = 0, $ledge = 0; $x < strlen($lbrks); $x++) $ledge = max($ledge, strrpos($match[2], $lbrks{$x}));
                if (!$ledge) $ledge = strlen($match[2]) - 1;
                $value = $match[1].substr($match[2], 0, $ledge + 1).$break.substr($match[2], $ledge + 1).$match[6];
              }
            } while ($store != $value);
          }
          if (!count($innbr)) $value = str_replace("\r", "<br />\n", $value);
        }
    }
    $drain .= $value;
  }
  return $drain;
}


function get_user_ip ()
{
	$SimpleIP = (isset($REMOTE_ADDR) ? $REMOTE_ADDR : getenv("REMOTE_ADDR"));

	$TrueIP = (isset($HTTP_X_FORWARDED_FOR) ? $HTTP_X_FORWARDED_FOR : getenv("HTTP_X_FORWARDED_FOR"));
	if ($TrueIP == "") $TrueIP = (isset($HTTP_X_FORWARDED) ? $HTTP_X_FORWARDED : getenv("HTTP_X_FORWARDED"));
	if ($TrueIP == "") $TrueIP = (isset($HTTP_FORWARDED_FOR) ? $HTTP_FORWARDED_FOR : getenv("HTTP_FORWARDED_FOR"));
	if ($TrueIP == "") $TrueIP = (isset($HTTP_FORWARDED) ? $HTTP_FORWARDED : getenv("HTTP_FORWARDED"));
	$GetProxy = ($TrueIP == "" ? "0":"1");

	if ($GetProxy == "0")
	{
	   $TrueIP = (isset($HTTP_VIA) ? $HTTP_VIA : getenv("HTTP_VIA"));
	   if ($TrueIP == "") $TrueIP = (isset($HTTP_X_COMING_FROM) ? $HTTP_X_COMING_FROM : getenv("HTTP_X_COMING_FROM"));
	   if ($TrueIP == "") $TrueIP = (isset($HTTP_COMING_FROM) ? $HTTP_COMING_FROM : getenv("HTTP_COMING_FROM"));
	   if ($TrueIP != "") $GetProxy = "2";
	};

	if ($TrueIP == $SimpleIP) $GetProxy = "0";

	// Return the true IP if found, else the proxy IP with a 'p' at the begining
	switch ($GetProxy)
	{
	   case '0':
	      // True IP without proxy
	      $IP = $SimpleIP;
	      break;
	   case '1':
	      $b = ereg ("^([0-9]{1,3}\.){3,3}[0-9]{1,3}", $TrueIP, $IP_array);
	      if ($b && (count($IP_array)>0))
	      {
	         // True IP behind a proxy
	         $IP = $IP_array[0];
	      }
	      else
	      {
	         // Proxy IP
	         $IP = "p".$SimpleIP;
	      };
	      break;
	   case '2':
	      // Proxy IP
	      $IP = "p".$SimpleIP;
	};
	return $IP;
}

function unhtmlentities ($string)  {
  $trans_tbl = get_html_translation_table (HTML_ENTITIES);
  $trans_tbl = array_flip ($trans_tbl);
  $ret = strtr ($string, $trans_tbl);
  return preg_replace('/&#(\d+);/me', 
     "chr('\\1')",$ret);
}

function dump($obj)
{
    echo "<div style='text-align: left;'><pre>";
    //if (is_object($obj) && @$obj->db) $obj->db = null;
    var_dump($obj);
    echo "</pre></div>";
}

function dump_r()
{
	
}



function ddump($obj)
{
	dump($obj);
	die();
}



?>