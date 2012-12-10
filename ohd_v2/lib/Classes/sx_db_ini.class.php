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
 * Represent ini-file in db structure across sxDB-class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 21, 2004
 * @version    1.30 Beta
 * @modified   $LastChangedDate$
 * @requires   sxDB
 */
if (!class_exists('sxDbIni')) {
class sxDbIni
{
	/**
	 * sxDÂ object
	 * @var sxDÂ
	 */
	var $db = false;
	
	/**
	 * Array for storying ini-values.
	 * @var array
	 */
	var $ini;

	/**
	 * Constructor
	 */
	function sxDbIni()
	{
		$this->db =& sxDB::instance();
	}
	
	/**
	 * Load data from database and return associative array.
	 * @return array
	 */
	function LoadIni($table_name, $where = '')
	{
		if ($where != '') $where = $this->db->compileWhereClause($where);
		// get data
		$this->db->q("
			SELECT 
			   option_group,
			   option_name,
			   option_value,
			   is_serialized
			FROM 
			   $table_name
			$where
			ORDER BY
			   option_group,
			   option_index,
			   option_name
		");
			
		// move data into array
		$ini = array();
		while ($data = $this->db->fetchAssoc())
		{
			if ($data['is_serialized']) $data['option_value'] = unserialize($data['option_value']);
			if (!isset($ini[$data['option_group']])) $ini[$data['option_group']] = array();
			$ini[$data['option_group']][$data['option_name']] = $data['option_value'];
		}
		
		return $this->ini = $ini;
	}
	
	function SaveIni($table_name, $ini, $where = array())
	{
		//if ($where != '') $where = $this->db->compileWhereClause($where);
		//TODO: merge $this->ini and $ini arrays - already done: imposeArray
		
		// save $ini to database
		foreach ($ini as $group => $options)
		{
			$index = 1;
			foreach ($options as $name => $value)
			{
				$is_serialized = is_array($value) ? 1 : 0;
				if ($is_serialized) $value = serialize($value);
				
				$prop_data = array (
					'option_group'  => $group,
					'option_name'   => $name,
					'option_index'  => $index,
					'option_value'  => $value,
					'is_serialized' => $is_serialized
				);
				
				$this->db->qI($table_name, $prop_data + $where, 'REPLACE');
				$index++;
			}
		}
	}
	
	/**
	 * Remove all items from current group.
	 */    
	function RemoveGroup($table_name, $group_name)
	{
		$this->db->qD($table_name, array('option_group' => $group_name));
	}
	
	/**
	 * Remove items defined in $ini.
	 */    
	function RemoveItems($table_name, $ini)
	{
		// iterate groups
		foreach ($ini as $group => $options)
		{
			foreach ($options as $name => $value)
			{
				$this->db->qD($table_name, array('option_group' => $group, 'option_name' => $name));
			}
		}    	
	}
	
	/**
	 * Combines two array so $target_array impose $stamp_array 
	 * (overrides existed items and add non-existed).
	 * @return array 
	 */    
	function ImposeArray($target_array, $stamp_array)
	{
		foreach ($stamp_array as $k1=>$v1)
		{
			if (!isset($target_array[$k1])) $target_array[$k1] = $v1;
			else
			{
				foreach ($v1 as $k2=>$v2) $target_array[$k1][$k2] = $v2;
			}
		}
		
		return $target_array;
	}   
	
}
}
?>