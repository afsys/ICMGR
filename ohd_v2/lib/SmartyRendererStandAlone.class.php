<?php
// +---------------------------------------------------------------------------+
// | This file is part of the OHD package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// | -----------------------------------------------------------               |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003 Sean Kerr.                                             |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+
class SmartyRendererStandAlone
{
	var $_smarty;
		
	var $template_dir;
	/**
	 * Create a new SmartyRenderer instance.
	 *
	 * @param templatedir Template directory
	 * @param template    Template file.
	 */
	function SmartyRendererStandAlone ($module_dir, $template = NULL, $delimiters_old = false)
	{
		if (!defined("SMARTY_DIR")) define("SMARTY_DIR",dirname(__FILE__)."/Smarty/");
		require_once(SMARTY_DIR . 'Smarty.class.php');
		$params =  array( 
			'cache_dir'            => SMARTY_CACHE_DIR, 
			'compile_dir'          => SMARTY_COMPILE_DIR, 
			'config_dir'           => $module_dir . '/configs/', 
			'debug_tpl'            => SMARTY_DEBUG_TPL, 
			'debugging_ctrl'       => DEBUGGING_CTRL, 
			'debugging'            => SMARTY_DEBUGGING, 
			'caching'              => SMARTY_CACHING, 
			'force_compile'        => SMARTY_FORCE_COMPILE,
		); 
		
		if ($delimiters_old == false) {
			$params['left_delimiter']  = '-{';
			$params['right_delimiter'] = '}-';
		}
		

		$this->_smarty =& new Smarty;
		$this->template_dir = $module_dir;
		// apply Smarty settings
		$keys  = array_keys($params);
		$count = sizeof($keys);

		for ($i = 0; $i < $count; $i++)
		{
			$this->_smarty->$keys[$i] =& $params[$keys[$i]];
		}
		
		// set template dir
		$this->_smarty->template_dir = $this->template_dir;
	}

	/**
	 * Render the view.
	 */
	function execute ()
	{

		if ($this->_template == NULL)
		{

			trigger_error('Template has not been specified.', E_USER_ERROR);
			exit;

		}



		$file = $this->template_dir . $this->_template;
		if (file_exists($file) && is_readable($file))
		{

			$this->_smarty->display($this->_template);

		} else
		{

			trigger_error("Template file $file does not exist or is not
						  readable.", E_USER_ERROR);
			exit;

		}

	}

	/**
	 * Retrieve an attribute.
	 *
	 * @param name Attribute name.
	 *
	 * @return a value, if an attribute with the given name exists, otherwise
	 *         NULL.
	 */
	function & getAttribute ($name)
	{

		$attribute =& $this->_smarty->get_template_vars($name);

		if ($attribute != NULL)
		{

			return $attribute;

		}

		return NULL;

	}

	/**
	 * Retrieve the Smarty instance this render is using.
	 */
	function & getSmarty ()
	{

		return $this->_smarty;

	}

	/**
	 * Remove an attribute.
	 *
	 * @param name Attribute name.
	 */
	function removeAttribute ($name)
	{

		$this->_smarty->clear_assign($name);

	}

	/**
	 * Set an attribute.
	 *
	 * @param name  Attribute name.
	 * @param value Attribute value.
	 */
	function setAttribute ($name, $value)
	{

		$this->_smarty->assign($name, $value);

	}

	/**
	 * Set an attribute by reference.
	 *
	 * @param name  Attribute name.
	 * @param value Attribute value.
	 */
	function setAttributeByRef ($name, &$value)
	{

		$this->_smarty->assign_by_ref($name, $value);

	}

}

?>