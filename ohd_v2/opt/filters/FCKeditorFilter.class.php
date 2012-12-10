<?php


// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * FCKeditorFilter
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 5, 2005
 * @version    1.00 Beta
 */
class FCKeditorFilter extends Filter 
{ 
	function execute (&$filterChain, &$controller, &$request, &$user)
	{
		// see if renderer is already loaded 
		$loaded = & $request->getAttribute('FCKeditor'); 
		if ($loaded == NULL)
		{
			require_once(OHD_LIB_DIR.'FCKeditor/fckeditor.php');

			$oFCKeditor = new FCKeditor ('');
			$oFCKeditor->ToolbarSet = 'Accessibility' ;
			$oFCKeditor->Skin = 'silver' ;
			$oFCKeditor->CanBrowse='false';
			$oFCKeditor->CanUpload='false';
			$oFCKeditor->BasePath = FCKeditor_DIR;
			
			$oFCKeditor->CanUpload='false';
			$request->setAttributeByRef('FCKeditor', $oFCKeditor);
			
			$filterChain->execute($controller, $request, $user);
			
			$request->removeAttribute('FCKeditor');
		}
		
	   else
		{
			$filterChain->execute($controller, $request, $user);
		}
	} 
}
?>