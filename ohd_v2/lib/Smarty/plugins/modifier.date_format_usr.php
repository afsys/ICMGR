<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *         - string: input date string
 *         - format: strftime format for output
 *         - default_date: default date if $string is empty
 * @link http://smarty.php.net/manual/en/language.modifier.date.format.php
 *          date_format (Smarty online manual)
 * @param string
 * @param string $mode enum ('full', 'short')
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_modifier_date_format_usr($timev, $mode = 'full', $un = null)
{
	if ($mode == 'full') $default_format = "%A %d %b %Y @ %H:%M by %s";
	else $default_format = "%d %b %Y";
	
	$format = null;
	if ($format === null) {
		if (!empty($_SESSION['attributes']['org.mojavi']['user_id']) && is_numeric($_SESSION['attributes']['org.mojavi']['user_id'])) {
			
			// get user options
			static $user_options = null;
			if ($user_options === null) {
				$user_id = $_SESSION['attributes']['org.mojavi']['user_id'];
				global $user;
				$user_options = $user->GetOptions(true, $user_id);
			}
			
			if ($mode === 'full' && isset($user_options['defaults']['time_format'])) {
				$format = $user_options['defaults']['time_format'];
			}
			elseif ($mode == 'short' && isset($user_options['defaults']['time_format_short'])) {
				$format = $user_options['defaults']['time_format_short'];
			}
			else {
				$format = $default_format;
			}
		}
		else {
			$format = $default_format;
		}
	}
	
	$_format = str_replace('%s', $un, $format);
	$res = strftime($_format, smarty_make_timestamp($timev));
	return $res;
}

/* vim: set expandtab: */

?>
