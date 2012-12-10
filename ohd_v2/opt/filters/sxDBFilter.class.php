<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Add sxDB filter
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
    
class sxDBFilter extends Filter 
{ 
     function execute (&$filterChain, &$controller, &$request, &$user)
     {
          // see if filter is already loaded
          $loaded =& $request->getAttribute('sxDB');
          
          if ($loaded == NULL)
          {
               require_once(OHD_LIB_DIR . 'Classes/sx_mysql.class.php');
               $db = new sxMySQL(DB_HOST, DB_NAME, DB_USER, DB_PASS, 3306, DB_PREF);
               if(!$db->isConnected()) die("Fatal error: could not connect to the database");
               $request->setAttributeByRef('sxDB', $db);

               // execute chain
               $filterChain->execute($controller, $request, $user);

               // remove renderer
               $request->removeAttribute('sxDB');
          }
          else
          {
               $filterChain->execute($controller, $request, $user);
          }
    } 
}
?>