<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage actindo_plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared', 'make_timestamp');

if (!isset($GLOBALS["_user_timezone"]))
{
	$user =& $smarty->get_template_vars('user');
	//$user_options = $user->getAttribute('user_options');
	$user_options =  $user->GetOptions();
	$GLOBALS["_user_timezone"] = $user_options["defaults"]["time_zone"];
}

/**
 * Smarty date_format_pretty modifier plugin
 * 
 * Type:     modifier<br>
 * Name:     date_format_pretty<br>
 * Purpose:  pretty-print file modification times<br>
 * Input:<br>
 *         - string: input date string
 *         - lang: Locale to use (de_DE,en_US, etc. null for default)
 *         - default_date: default date if $string is empty
 * @author Patrick Prasse <pprasse@actindo.de>
 * @version $Revision: 1.3 $
 * @param string
 * @param string
 * @param string
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_modifier_date_format_pretty($string, $lang = null, $default_date = null, $inc_yesterday = true)
{
  if( $string != '' && $string != '0000-00-00' )
    $date = smarty_make_timestamp( $string );
  elseif( isset($default_date) && $default_date != '' )
    $date = smarty_make_timestamp( $default_date );
  else
    return;

  //kill me for globals -) or better explain how to do it in another way 
  $user_timezone = $GLOBALS["_user_timezone"]*3600;
  //now get shift between user timezone and server timezone
  $server_timezone = date("Z");
  
  $shift = $user_timezone - $server_timezone;
  
  $today00      = strtotime('today 00:00:00');
  $yesterday00  = strtotime('yesterday 00:00:00');
  $two_days_ago = strtotime('-2 days 00:00:00');
  $six_days_ago = strtotime('-6 days 00:00:00');
  $one_year_ago = strtotime('-1 year 00:00:00');
	
	if ($shift != 0)
	{
		//convert to my local time !
		$date += $shift;
		//calculate difference in days
		if (date("z",time()) != date("z",time() + $shift))
		{
			$days_diff = ($shift>0)?1:-1;
		}
		$days_diff *= 24*3600;
		
    $today00      += $days_diff;
    $yesterday00  += $days_diff;
    $two_days_ago += $days_diff;
    $six_days_ago += $days_diff;
    $one_year_ago += $days_diff;
	}

  $save_lang = setlocale( LC_TIME, 0 );
  if( !isset($lang) )
    $l = setlocale( LC_TIME, 0 );
  else
  {
    setlocale( LC_TIME, $lang );
    $l = $lang;
  }

  $langs = array(
    'de' => array( 'Gestern', 'Vorgestern' ),
    'en' => array( 'Yesterday', '' ),
    'C'  => array( 'Yesterday', '' ),
  );
  $l = split( '_', $l );

  if( $date > $today00 )
  	if ($inc_yesterday == true) 
  	{
	    $d = strftime( 'Today %m.%d.%y at %H:%M', $date );
	  } else {
	    $d = strftime( '%m.%d.%y at %H:%M', $date );
	  }
  elseif ($inc_yesterday == true)
  {
	  if( $date > $yesterday00 )
	    $d = $langs[$l[0]][0].' '.strftime( ' %m.%d.%y at %H:%M', $date );
	  elseif( $date > $two_days_ago && $l[0] == 'de' )   // only for de_* locales
	    $d = $langs[$l[0]][1].' '.strftime( ' %m.%d.%y at %H:%M', $date );
	  elseif( $date > $six_days_ago )
	    $d = strftime( '%A, %H:%M', $date );
	  elseif( $date > $one_year_ago )
	    $d = strftime( '%d %b, at %H:%M', $date );
	  else
	    $d = strftime( '%d %b %Y, at %H:%M', $date );
	}
	else
	{
		$d = strftime( '%d %b %Y', $date );
	}
  if( isset($lang) )
    setlocale( LC_TIME, $save_lang );

  return $d;
}
?>