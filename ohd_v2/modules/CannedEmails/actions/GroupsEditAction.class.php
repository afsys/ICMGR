<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to edit CE groups.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 29, 2005
 * @version    1.00 Beta
 */
    
class GroupsEditAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        // make aliases
        $db =& sxDb::instance();
        
        $cat_caption = $request->getParameter('cat_caption');
        $cat_desc    = $request->getParameter('cat_desc');
        $cat_id      = $request->getParameter('cat_id');
        /*
        $id = !is_null($request->getParameter('id'))?intval($request->getParameter('id')):false;
        $delete = $request->getParameter('delete');*/
        
        
    	if ($request->getParameter('edit_group'))
    	{
    		// check submitted name
    		if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name))
    		{
    			//$errors['groupName'] = 'Please choose a non-empty group name consisting of only letters and numbers';
    		}
    		else
    		{
    			/*
    			//check that groupname doesn't already exist
    			$query = 'SELECT id FROM ohd_groups WHERE name=?';
    			$queryValues = array($name);

    			$gid = $db->getOne($query, $queryValues);

    			if (!DB::isError($gid))
    			{
    				//trying to edit the groupname to a name that already exists in the database
    				if ($gid != $id && $gid)
    				{
    					$errors['groupName'] = 'This group already exists in the database';
    				}
    			}
    			
    			else 
    			{
    				//hmm, something went wrong with db
    				$errors['groupName'] = 'There was an error with the database. Please try again';
    			}*/
    		}  
    		
    		if (count($errors) == 0)
    		{
    			// get group_id
    			if (!empty($cat_id)) 
    			{
    				$new_cat_id = $cat_id;
    				$op_name = "REPLACE";
    			}
    			else
    			{
    				$new_cat_id = $db->getNextId('#_PREF_canned_emails_categories', 'cat_id');
    				$op_name = "INSERT INTO";
    			}
    			
    			
    			$group_data = array (
    				'cat_id'      => $new_cat_id,
    				'cat_caption' => $cat_caption,
    				'cat_desc'    => addslashes(htmlspecialchars(trim($cat_desc))),

    			);
    			
    			$db->qI('#_PREF_canned_emails_categories', $group_data, $op_name);
    			/*
    			echo "<pre>";
    			var_dump($group_data);
    			die('dda');
    				

    			//everything is verified, now update the db
    			$queryValues = array(	'name' => $name,
    									'comment' => addslashes(htmlspecialchars(trim($comment))),
    									'system_privs' => $system_privs,
    									'group_privs' => $group_privs
    								);

    			$res = $db->autoExecute('ohd_groups', $queryValues, DB_AUTOQUERY_UPDATE, 'id='.$id);

    			if (DB::isError($res))
    			{
    				//hmm, something went wrong with db
    				$errors['groupName'] = 'There was an error with the database. Please try again';
    			}
    			
    			else 
    			{
    				$request->setAttribute('error', 'Group updated successfully');
    			}
    			
    			if ($old_name != $name)
    			{
    				//group name has changed -- have to update user groups column to
    				//keep everything running smoothly
    				$query = 'UPDATE ohd_users SET groups=REPLACE(groups, ?, ?)';
    				$db->query($query, array($old_name, $name));
    			}*/
    		}    
    		
    		header('Location: index.php?module=CannedEmails&action=GroupsList');
    		exit;
    		
    	}        
        
       
        /*echo "<pre>";
        var_dump($_POST);
        var_dump($errors);
        die('ddd');*/
        
        return VIEW_SUCCESS;  
    }
    
    function getDefaultView (&$controller, &$request, &$user)
    {
        return VIEW_SUCCESS;
    }

    function handleError (&$controller, &$request, &$user)
    {
        // don't handle errors, just redirect to error 404 action
        $controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
        return VIEW_NONE;
    }

    function registerValidators (&$validatorManager, &$controller, &$request, &$user)
    {

    }
    
    function getPrivilege()
    {
        return NULL;
    }
    
    function isSecure()
    {
        return TRUE;    
    }
}

?>