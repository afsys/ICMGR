<?php

/**
 * Smarty {trans} function plugin
 *
 * Type:     function<br>
 * Name:     trans<br>
 * Purpose:  evaluate a template variable as a template<br>
 * @param array
 * @param Smarty
 */
function smarty_function_trans($params, &$smarty)
{
	__('');
	if (empty($params['action'])) $params['action'] = 'get_trasnlation';

    switch ($params['action'])
    {
    	case 'get_trasnlation':
			if (!isset($params['str'])) {
				$smarty->trigger_error("trans: missing 'str' parameter");
				return;
			}

			if($params['str'] == '') {
				return '';
			}

			return __($params['str']);
    		break;
    		
    	case 'get_parameter':
    		global $lang_params;
			if (!isset($params['parameter_name'])) {
				$smarty->trigger_error("trans: missing 'parameter_name' parameter");
				return;
			}  
			
			return __($lang_params[$params['parameter_name']]);
    		break;
    }
    
}

/* vim: set expandtab: */

?>
