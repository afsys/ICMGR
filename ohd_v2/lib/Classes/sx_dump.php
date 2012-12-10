<?php
//
// +------------------------------------------------------------------------+
// | SX common modules                                                      |
// +------------------------------------------------------------------------+
// | Copyright (c) 2004 Konstantin Gorbachov                                |
// | Email         slyder@bk.ru                                             |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
    
    
/**
 * Dump module for PHP 4.2.0
 *
 * Class offer different operation for dump functions.
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Dev
 */
class sxDump
{
    /**
     * Determine, if all users will be see dump data, or users
     * defined in ... array only.
     * @var boolean
     */
    var $isUserListEnabled = false;
    
    /**
     * Array of allowed IP-addreses for users which will be see 
     * dump data if $isUserListEnabled == true;
     */
    var $aAllowedUsers = array();
     
    /**
     * Field stores all dump data for further output.
     */
    var $sDumpItems;
    
    /**
     * Constructor
     */    
    function sxDump($isUserListEnabled, $aAllowedUsers = null)
    {
        $this->sDumpItems = array();
        $this->isUserListEnabled = $isUserListEnabled;
        if ($aAllowedUsers != null) $this->aAllowedUsers = $aAllowedUsers;
    }    
    
    /**
     * Add user into array of allowed users.
     * @param     string    $ip    user ip-addres for adding in array of allowed users
     */    
    function AddAllowedUser($ip)
    {
        $aAllowedUsers[] = $ip;
    }

    /**
     * Add user into array of allowed users.
     * @param     object    $obj    object for dump
     */    
    function Add($obj)
    {
        $this->sDumpItems[] = print_r($obj, true);
    }

    /**
     * Write all dump data into output stream
     */     
    function ShowDump()
    {
        echo "<pre>";
        foreach ($this->sDumpItems as $item)
        {
            echo $item;
        }
        echo "</pre>";
    }
    
    /**
     * Output dump data and terminate sctipt execution.
     * @param     object    $obj    object for dump
     */     
    function Stop($obj = null)
    {
        if ($obj) $this->Add($obj);
        $this->ShowDump();
        die();
    }
}


function dump($obj) {
}