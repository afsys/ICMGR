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
 * MySQL database module for PHP 4.2.0
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 21, 2004
 * @version    1.64 Beta
 */
if (!class_exists('sxMySQL')) {
class sxMySQL
{
	/**
	 * Determine is class connected to DB or not
	 * @var boolean
	 */
	var $isConnected = false;
	
	/**
	 * Contains connected DB resource
	 * @var resource 
	 */
	var $dbHnd;
	
	/**
	 * Contains user name, password, database name, host and 
	 * port for DB connection
	 * @var string
	 */
	var $user;
	var $pass;
	var $database;
	var $host;
	var $port;
	
	/**
	 * Contains last query result identifier
	 * @var number
	 */
	var $last_q_res;
	
	/**
	 * Contains last query string. Using for dump functions.
	 * @var string
	 */
	var $last_query_str;
	var $last_query_exec_time;
	
	/**
	 * If true then class throw exception on query error
	 * @var boolean
	 */
	var $exception_on_error = true;
	
	/**
	 * Turn on queries dumping
	 * @var boolean
	 */
	var $dump_on = false;
	
	/**
	 * Define dump target
	 * @var string enum (page, inner, file, window)
	 */
	var $dump_target = 'file';
	
	/**
	 * Stores dump data if $dump_target == 'inner', and path to file if $dump_target == 'file'
	 * @var string
	 */          
	var $dump_data = "c:\\dump.sql";
	
	/**
	 * Stores count of executed queries during the page loaging.
	 * @var string
	 */          
	var $queries_count = 0;
	
	/**
	 * Class does not execute quertion if flag equal to true;
	 * @var string
	 */          
	var $skip_queries_execution = false;
	
	/**
	 * Define table prefix in database.
	 * @var string
	 */          
	var $table_pref = null;
	
	/**
	 * Define table prefix in query for replacing by $table_pref.
	 * @var string
	 */          
	var $table_pref_mask = '#_PREF_';
	
	/**
	 * Define should class calculate total queries execution time or not.
	 * @var string
	 */          
	var $calc_exex_time = true;
	var $queries_exec_time = 0;
	
	/**
	 * Constructor
	 */
	function sxMySQL($host, $database, $user, $pass, $port = 3306, $table_pref = null)
	{
		$this->user = $user;
		$this->pass = $pass;
		$this->host = $host;
		$this->database   = $database;
		$this->port       = $port;
		$this->table_pref = $table_pref;
			
		$dbHnd = mysql_connect($host . ":" . $port, $user, $pass);
		if (!$dbHnd)
		{
			if ($this->exception_on_error) 
			{
				trigger_error("sxMySQL error :: could not connect to server '". $host ."'", E_USER_ERROR);         
			}
			else return;
		}
		
		$dBase = mysql_select_db($database, $dbHnd);
		if (!$dBase) 
		{
			if ($this->exception_on_error) 
			{
				trigger_error("sxMySQL error :: could not connect to database '". $database ."'", E_USER_ERROR);      
			}
			else return;
		}
		$this->dbHnd = $dbHnd;
		$this->isConnected = true;
	}
	
	/**
	 * Returns error description for last operation
	 * @return string
	 */
	function getError()
	{
		return (@mysql_error($this->dbHnd));
	}
	
	/**
	 * Returns DB-connection status
	 * @return boolean
	 */
	function isConnected()
	{
		return $this->isConnected;
	}
	
	/**
	 * Executes query and returns query identifier
	 * @param     string    $query          SQL-query string
	 * @param     object    could be string or array:
	                        string          SQL-where clause
	                        array           specialy formated array for where-clause compiling
	                        array (         extended array for all parameters
	                           '#WHERE' =>
	                           '#ORDER' =>
	                           '#GROUP' =>
	                           '#LIMIT' =>
	                        )
	 * @return    array     query           identifier
	 */
	function query($query, $params = null)
	{

		$this->queries_count++;
		
		mysql_select_db($this->database, $this->dbHnd);
		
		// build query
		$query .= $this->compileParams($params);

		// replace prefix
		if ($this->table_pref != null) $query = str_replace($this->table_pref_mask, $this->table_pref, $query);
		
		$this->last_query_str = $query;
		if ($this->dump_on) $this->dump2target();
		
		// exec query
		if ($this->calc_exex_time) $t_s = GetMicroTime();
		if (!$this->skip_queries_execution) $this->last_q_res = mysql_query($this->last_query_str, $this->dbHnd);
		else {
			if (preg_match('/^\s*SELECT*/m', $this->last_query_str)) $this->last_q_res = mysql_query($this->last_query_str, $this->dbHnd);
			else $this->last_q_res = 'skip_queries_execution is on';
		}
		if ($this->calc_exex_time) 
		{
			$this->last_query_exec_time = GetMicroTime() - $t_s;
			$this->queries_exec_time += GetMicroTime() - $t_s;
		}

		if (!$this->last_q_res && $this->exception_on_error) 
		{
			//trigger_error("sxMySQL error :: ". mysql_error($this->dbHnd) ."\n<br>". ($this->dumpQuery($this->last_query_str, true)), E_USER_ERROR);         
			trigger_error("sxMySQL error :: ". mysql_error($this->dbHnd), E_USER_ERROR);         
		}
		
		return $this->last_q_res;
	}  
	
	function query_($query, $params = null)
	{
		$res = $this->query($query, $params);
		$this->dumpQuery($this->last_query_str);
		return $res;
	}    
	
	function q ($q, $params = null) { return $this->query ($q, $params); }
	function q_($q, $params = null) { return $this->query_($q, $params); }
	
	function dump2target()
	{
		switch ($this->dump_target)
		{
			case 'page':
				break;
			
			case 'inner':
				break;
			
			case 'file':
				$file_handle = fopen($this->dump_data, "a+");
				if ($file_handle)
				{
					//include_once 'sqlhighlighter/SqlHighlighter.class.php';
					//$mySQLhighlighted = new SqlHighlighter('E:/Work/http/home/common_files/inc/classes/sqlhighlighter/');
					//$query_f = $mySQLhighlighted->highlight($query);
					if ($this->queries_count == 1) {
						fwrite($file_handle, "\n\n\n");
						fwrite($file_handle, "\n**********************************************************");
						fwrite($file_handle, "\n* Dump session: ". date("Y-m-d H:i:s") ."");
						fwrite($file_handle, "\n**********************************************************");
					}
					
					fwrite($file_handle, "\n\n\nQuery #". $this->queries_count ." dump:\n\n");
					fwrite($file_handle, $this->dumpQuery($this->last_query_str, false));
					fclose($file_handle);
				}
				break;
				
			case 'window':
				break;
		}
	}
	
	/**
	 * Executes script 
	 * @TODO make queries spliting more intelectual
	 * @param     string     $query SQL-query string
	 * @return    boolean    true - if script executed successfully else returns false
	 */
	function script($sql)
	{
		$queries =  preg_split('/;\s*[\n$]/m', $sql);
		$this->query("BEGIN;");
		foreach ($queries as $q) {
			if (trim($q) == "") continue;
			
			$this->query($q);
			$is_error = $this->IsError();
			if ($is_error) {
				$this->query("ROLLBACK;");
				return false;
			}
		}
		$this->query("COMMIT;");
		return true;
	}
	
	/**
	 * Get the ID generated from the previous INSERT operation.
	 * @param     string    $query SQL-query string
	 * @return    array     values of first row columns or false if there is error or none rows
	 */
	function lastInsertId($dbHnd = null)
	{
		if ($dbHnd === null) 
		{
			if ($this->dbHnd === null) return false;
			$dbHnd = $this->dbHnd;
		}
		return mysql_insert_id($dbHnd);
	}
	
	/**
	 * Executes query and returns first line array of query result
	 * @param     string    $query SQL-query string
	 * @return    array     values of first row columns or false if there is error or none rows
	 */
	function queryFetch($query)
	{
		$this->query($query);
		if (!$this->last_q_res) return false;
		return mysql_fetch_array($this->last_q_res);
	}
	
	/**
	 * Debug version of queryFetch function
	 */
	function queryFetch_($query)
	{
		$this->dumpQuery($this->last_query_str);
		return $this->queryFetch($query);
	}
	
	/**
	 * Returns first line array of query result
	 * @param   string   $table        table name for inserting data
	 * @param   array    $values       associative array of row names and values; format: (name1=>value1, name2=>value2, ...)
	 * @param   string   $type         query type (insert, replace or update)
	 * @param   string   $where        where clause for update and replace queries
	 * @return  array                  values of first row columns or false if there is error or none rows
	 */
	function queryInsert($table, $values, $type = "INSERT INTO", $where = "")
	{
		// compile SET clause
		$query = "";
		foreach ($values as $k=>$v)
		{
			if ($query != "") $query .= ",\n";
			if ($v === 'NULL' || $v === null) $v = 'NULL';
			else 
			{
				$v = quote_smart($v);
			}
			$query .= "    $k = $v";
		}
		
		// compile WHERE clause
		$where_clause = $this->compileWhereClause($where);
		
		// compile full query-string
		$query = $type . " $table\n  SET \n". $query ."\n". $where_clause;
		
		// execute query
		$res = $this->query($query);
		return $res;
	}
	
	function queryInsert_($table, $values, $type = "INSERT INTO", $where = "")
	{
		$res = $this->queryInsert($table, $values, $type, $where);
		$this->dumpQuery($this->last_query_str);
		return $res;
	}
	
	function qI($table, $values, $type = "INSERT INTO", $where = "")
	{
		return $this->queryInsert($table, $values, $type, $where);
	}
	
	function qI_($table, $values, $type = "INSERT INTO", $where = "")
	{
		return $this->queryInsert_($table, $values, $type, $where);
	}
	
	/**
	 * Returns first line array of query result
	 * @param   string   $table       table name for inserting data
	 * @param   array    $where       associative array of row names and values; format: (name1=>value1, name2=>value2, ...)
	 */
	function queryDelete($table, $where = null)
	{
		// compile WHERE clause
		$where_clause = $this->compileWhereClause($where);
		
		// compile query-string
		$query = "DELETE FROM $table\n $where_clause";
		
		$this->query($query);
		if (!$this->last_q_res) return false;
		return true;
	}
	
	function queryDelete_($table, $where = null)
	{
		$res = $this->queryDelete($table, $where);
		$this->dumpQuery($this->last_query_str);
		return $res;
	}
	
	function qD($table,  $where = null) { return $this->queryDelete($table, $where);  }
	function qD_($table, $where = null) { return $this->queryDelete_($table, $where); }
	
	/**
	 * Returns first item from query
	 * @param     string     $query     sql query
	 * @return    sttring    querty result
	 */
	function getOne($query, $where = "")
	{
		$res = $this->query($query, $where);
		return $this->result(0, $res);
	}

	function getOne_($query, $where = "")
	{
		$res = $this->query_($query, $where);
		return $this->result(0, $res);
	}

	
	/**
	 * Returns item by index from fetched array
	 * @param     number     $index item index from array
	 * @param     number     $qr query result identifier
	 * @return    object     item from array
	 */
	function result($index = 0, $qr = 0)
	{
		if ($qr === 0) $qr = $this->last_q_res;
		$res = mysql_fetch_array($qr);
		if ($index >= 0 && $index < count($res)) return $res[$index];
		else if ($this->exception_on_error) 
		{
			trigger_error("sxMySQL warning :: index out of range in result-funtion", E_USER_WARNING);
		}
		else return false;
	}
	
	/**
	 * Execute query and returns item from fetched array
	 * @param     query      $sql string
	 * @param     number     $index item index from array
	 * @return    object     item from array
	 */
	function queryResult($query, $index = 0)
	{
		$qr = $this->query($query);
		$res = mysql_fetch_array($qr);
		if ($index >= 0 && $index < count($res)) return $res[$index];
		else if ($this->exception_on_error) 
		{
			trigger_error("sxMySQL warning :: index out of range in result-funtion", E_USER_WARNING);
		}
		else return false;
	}  
	
	function queryResult_($query, $index = 0)
	{
		$res = $this->queryResult($query);
		$this->dumpQuery($this->last_query_str);
		return $res;
	}
	
	function qR($query, $index = 0) { return $this->queryResult($query, $index); }
	
	function qR_($query, $index = 0) { return $this->queryResult_($query, $index); }
	
	/**
	 * Make associative fetching of last result or defined query idenfirier
	 * @param    number   $qr query result identifier
	 * @return   array    associative array of returned data or false
	 */
	function fetchAssoc($qr = 0)
	{
		if ($qr === 0) $qr = $this->last_q_res;
		return mysql_fetch_assoc($qr);
	}
	
	/**
	 * Make fetching of last result or defined query idenfirier
	 * @param     number    $qr query result identifier
	 * @return    array     of returned data or false if error occured.
	 */
	function fetchArray($qr = 0)
	{
		if ($qr === 0) $qr = $this->last_q_res;
		return mysql_fetch_array($qr);
	}
	
	/**
	 * Return rows number
	 * @param     number    $qr query result identifier
	 * @return    array     associative array of returned data or false
	 */
	function numRows($qr = 0)
	{
		if ($qr === 0) $qr = $this->last_q_res;
		return mysql_num_rows($qr);
	}   
	
	/**
	 * Get number of affected rows
	 * @param     number    $qr query result identifier
	 * @return    array     associative array of returned data or false
	 */
	function AffectedRows($qr = 0)
	{
		if ($qr === 0) $qr = $this->last_q_res;
		return mysql_affected_rows($qr);
	}
	
	/**
	 * Return associative array of query rows
	 * @param     string    $table_name        table name
	 * @return    array                        array of table fields
	 */
	function GetTableFields($table_name)
	{
		$fields = mysql_list_fields($this->database, $table_name, $this->dbHnd);
		$columns   = mysql_num_fields($fields);
		$names = array();
		for ($i = 0; $i < $columns; $i++) $names[] = mysql_field_name($fields, $i);
		return $names;
	}	
	
	/**
	 * Return associative array of query rows
	 * @param     number    $qr query result identifier
	 * @return    array     array of returned data or false if error occured.
	 */
	function GetQueryFields($qr = 0)
	{
		if ($qr === 0) $qr = $this->last_q_res;
		$rows = array();
		for ($i = 0, $num = mysql_num_fields($qr); $i < $num; $i++) {
			$rows[] = mysql_field_name($qr, $i);
		}
		return $rows;
	}
	
	/**
	 * Returns next id number for records or 1 if there is no records.
	 * @param   string   $table       table name for selection
	 * @param   string   $row         table row name for selection
	 * @param   string   $where       where clause
	 */
	function getNextId($table, $row, $where = '')
	{
		$where = $this->compileWhereClause($where);
		$q = "SELECT IFNULL(MAX($row)+1,1) FROM $table $where";
		$this->query($q);
		return $this->result();
	}
	
	function getNextId_($table, $row, $where = NULL)
	{
		$where = $this->compileWhereClause($where);
		$q = "SELECT IFNULL(MAX($row)+1,1) FROM $table $where";
		$this->query_($q);
		$res = $this->result();
		echo "<pre style='text-align: left;'>Result: '$res'<pre>\n";
		return $res;
	}
	
	/**
	 * Returns count of records in table.                          
	 * @param   string   $table       table name for selection     
	 * @param   string   $row         table row name for selection 
	 * @param   string   $where       where clause                 
	 */
	function getCount($table, $where = NULL, $row = '*')
	{
		$q = "SELECT COUNT($row) FROM $table";
		$this->query($q, $where);
		return $this->result();
	}
	
	function getCount_($table, $where = NULL, $row = '*')
	{
		$q = "SELECT COUNT($row) FROM $table";
		$this->query_($q, $where);
		return $this->result();
	}
	

	function compileParams($params)
	{
		if (is_string($params)) return $this->compileWhereClause($params);
		else if (is_array($params)) 
		{
			if (isset($params['#GROUP']))  $group_by  = $this->compileGroupBy($params['#GROUP']); else $group_by = "";
			if (isset($params['#ORDER']))  $order_by  = $this->compileOrderBy($params['#ORDER']); else $order_by = "";
			if (isset($params['#LIMIT']))  $limit_by  = $this->compileLimitBy($params['#LIMIT']); else $limit_by = "";
			if (isset($params['#HAVING'])) $having    = $this->compileWhereClause($params['#HAVING'], 'HAVING'); else $having = "";
			
			if (empty($group_by) && empty($having) && empty($order_by) && empty($limit_by) && !isset($params['#WHERE'])) return $this->compileWhereClause($params);
			else return $this->compileWhereClause(@$params['#WHERE']).$group_by.$having.$order_by.$limit_by;
		}
		
		return "";
	}

	
	function compileGroupBy($group_by)
	{
		if (is_array($group_by)) $group_by = implode(',', $group_by);
		else if (!is_scalar($group_by)) return "";
		return "\nGROUP BY $group_by ";
	}
	
	function compileOrderBy($order_by)
	{
		if (is_array($order_by)) $order_by = implode(',', $order_by);
		else if (!is_scalar($order_by)) return "";
		return "\nORDER BY $order_by ";
	}
	
	function compileLimitBy($limit_by)
	{
		if (is_array($limit_by)) $limit_by = implode(',', $limit_by);
		else if (!is_scalar($limit_by)) return "";
		return "\nLIMIT $limit_by ";
	}
	
	/**
	 * Make where clause from special formated array of from string.
	 * @param     object    $where   where-clause string or associative array with where-items
	 *                      ex: array (name => 'Alex', lastname => 'Ivanov')        - "WHERE name = 'Alex' AND lastname = 'Ivanov'
	 *                      ex: array (name => 'Alex', rec_date => 'NOW()')         - "WHERE name = 'Alex' AND rec_date = NOW() 
	 *                      ex: array (name => 'Alex', zipcode => array(223, 451))  - "WHERE name = 'Alex' AND zipcode IN (223, 451)
	 *                      ex: array (name => 'Alex', zipcode => array('NOT IN', array(223, 451)))  - "WHERE name = 'Alex' AND zipcode NOT IN (223, 451)
	 *                      ex: array (name => array('LIKE', 'Alex%'))              - "WHERE name LIKE 'Alex%'
	 *                      ex: array (zipcode => array('>', 223))                  - "WHERE zipcode > 223
	 * @param     string    $type_where   'WHERE' or 'HAVING' value
	 * @return    string    formated where SQL-string part
	 */
	function compileWhereClause($where, $type_where = 'WHERE')
	{
		// compile WHERE clause
		if (is_array($where)) {
			$where_clause = $this->compileWhereClause_($where);
		}
		else {
			$where_clause = $where;
		}
		
		if ($where_clause !== null && trim($where_clause)) $where_clause = "\n  $type_where $where_clause";
		
		return $where_clause;
	}
	 
	/**
	 * Auxilary recurentive function for compileWhereClause.
	 *  $ticket_where = array (
	 *     '#DELIM' => 'AND',
	 *     'closed_at'   => '',
	 *     'status'      => array('!=', 'Closed'),
	 *     'AND' => array (
	 *        '#DELIM' => 'OR',
	 *        'assigned_to'   => 1,
	 *        'owner_user_id' => 2
	 *     ),
	 *  );
	 */
	function compileWhereClause_($where)
	{
		$OR  = isset($where['OR'])  ? $where['OR']  : null; unset($where['OR']);
		$AND = isset($where['AND']) ? $where['AND'] : null; unset($where['AND']);

		if (isset($where['#DELIM'])) {
			$delimiter = $where['#DELIM'];
			unset($where['#DELIM']);
		}
		else {
			$delimiter = "AND";
		}
		
		// add plain SQL statement
		if (isset($where['#PLAIN'])) {
			$where_clause = $where['#PLAIN'];
			unset($where['#PLAIN']);
		}
		else $where_clause = "";
		
		// convert array into plain SQL statement and add to query string
		if (isset($where['#ARRAY'])) {
			$array_in_plain = $this->compileWhereClause_($where['#ARRAY']);
			$where_clause = empty($where_clause) ? "($array_in_plain)" : "($where_clause) $delimiter ($array_in_plain)";
			unset($where['#ARRAY']);
		}
		
		// convert arrays into plain SQL statement and add to query string
		if (isset($where['#ARRAYS'])) {
			foreach ($where['#ARRAYS'] as $sql_a) {
				$array_in_plain = $this->compileWhereClause_($sql_a);
				$where_clause = empty($where_clause) ? "($array_in_plain)" : "($where_clause) $delimiter ($array_in_plain)";
			}
			unset($where['#ARRAYS']);
		}
		
		foreach ($where as $k=>$v)
		{
			if ($where_clause != "") $where_clause .= "\n $delimiter";
			
			// is array: field IN (1, 2, 3)
			if (is_array($v))
			{
				switch (strval($v[0]))
				{
					// ex4, ex5
					case 'LIKE':
					case '>':
					case '>=':
					case '<':
					case '<=':
					case '!=':
						$where_clause .= " $k {$v[0]} ". quote_smart($v[1]);
						break;
						
					case 'NOT IN':
						foreach ($v[1] as $kk=>$vv) $v[1][$kk] = quote_smart($vv);
						$where_clause .= " $k NOT IN (". implode(',', $v[1]) .")";
						break;
					
					// ex3 (IN)
					default:
						foreach ($v as $kk=>$vv) $v[$kk] = quote_smart($vv);
						$where_clause .= " $k IN (". implode(',', $v) .")";
						break;
				}
			}
			// other cases
			else 
			{
				
				if ($v === null) $where_clause .= " $k IS NULL";
				else 
				{
					switch (strval($v))
					{
						case "IS NULL":
							$where_clause .= " $k IS NULL";
							break;
						
						case "IS NOT NULL":
							$where_clause .= " $k IS NOT NULL";
							break;

						default:
							$v = quote_smart($v);
							$where_clause .= " $k = $v";
							break;
					}
				}
			}
		}
		
		if ($OR)
		{
			$OR_where_clause = $this->compileWhereClause_($OR);
			$where_clause = $where_clause ? "($where_clause) OR ($OR_where_clause)"  : $OR_where_clause;
		}
		
		if ($AND)
		{
			$AND_where_clause = $this->compileWhereClause_($AND);
			$where_clause = $where_clause ? "($where_clause) AND ($AND_where_clause)" : $AND_where_clause;
		}		
		
		return $where_clause;
	}

	
	/**
	 * Make SQL-string color highlithing.
	 * @param     string    $query   SQL-string
	 * @param     bool      $show    if true - then formated query will be write into output stream
	 * @return    string    formated SQL-string
	 */
	function dumpQuery($query, $show = true)
	{
		// remove trailing spaces
		$lines = explode("\n", $query);
		$max_sp_len = -1; // max spaces length
		foreach ($lines as $line)
		{
			if (strlen(trim($line)) == 0) continue;
			preg_match("/^ */", $line, $match);
			$max_sp_len = $max_sp_len > strlen($match[0]) || $max_sp_len == -1 ? 
					strlen($match[0]) : $max_sp_len;
		}
		if ($max_sp_len > 4)
		{
			$max_sp_len -= 2;
			$query = implode("\n", $lines);
			$query = preg_replace("/^ {". $max_sp_len ."}/m", "", $query);
		}
		
		// TODO: query formating
		
		// query output
		if ($show)
		{
			/*
			// get count of affected rows
			if (preg_match("/^\s*SELECT/si", $query)) $iAffRows = $this->numRows();
			else $iAffRows = $this->affectedRows();
			Affected rows: </?= $iAffRows ?/> */
			?>
			<pre style='text-align: left;'><strong>Query #<?= $this->queries_count; ?> dump:</strong>
				<?= $query ?>
			</pre>
			<?php
		}
		// return formated query string
		return $query;
	}
	
	/**
	 * Return associative array of query rows
	 * @param     handle      $hnd connection handle
	 * @return    boolean     true on error, else false
	 */
	function IsError($hnd = 0)
	{
		if ($hnd === 0) $hnd = $this->dbHnd;
		return mysql_errno($hnd) !== 0;
	}
}


if (!function_exists("GetMicroTime")) {
	function getmicrotime(){ 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
	}
}


/*
 * ADDITIONAL FUNCTIONS
 */


function quote_smart($value) 
{
	if ($value == 'NOW()') return $value;
	
	// stripslashes, if not already :)
	if (true === array_search($value, $_REQUEST))
	{
		if (get_magic_quotes_gpc()) $value = stripslashes($value);
	}
	// quote if not integer
	//if (!is_numeric($value)) 
	$value = "'" . mysql_real_escape_string($value) . "'";
	return $value;
}

/*
function quote_smart($value) 
{
	// stripslashes
	if (get_magic_quotes_gpc()) $value = stripslashes($value);
	// quote if not integer
	if (!is_numeric($value)) $value = "'" . mysql_real_escape_string($value) . "'";
	return $value;
} /**/




/**
 * Функция разделяет SQL-запросы по разделителю и возвращает массив запросов
 * Так же вырезаются все комментарии. Пустые запросы игнорируются.
 *
 * @param    string  $sql    SQL запросы
 * @param    string  $delim  разделитель
 * @return   array
 *
 * @author   Nasibullin Rinat <rin at starlink ru>
 * @charset  ANSI
 * @version  1.0.1
 */
function sql_split($sql, $delim = ';')
{
	// заменяет всё, что:
	//  - находится между комментариями вида /*!ddddd ... */  (выполнение части кода SQL запроса в зависимости от версии MySQL)
	//  - находится в одинарных или двойных кавычках на заглушки
	// кавычки могут экранироваться, например, "test\"test"
	preg_match_all('/\/\*\!\d{5}[ \r\n\t].*?\*\/ | (["\'`])(?:(?:\\\\\1|[^\\1])*?)\\1/sx', $sql, $m);

	$ph = array(); #placeholders
	foreach ($m[0] as $k => $v)
	{
		$ph[] = '@[' . $k . ']@';
	}

	// заменяем содержимое в кавычках на временные заглушки
	$sql = str_replace($m[0], $ph, $sql);

	// вырезаем все комментарии в стиле /*comment*/, --comment, #comment
	$sql = preg_replace('/\/\*.*?\*\/ | (--|\#).*?([\r\n]+)/sx', '$2', $sql);

	$r = array();
	foreach (explode($delim, $sql) as $q)
	{
		if ($q = trim($q))
		{
			$r[] = str_replace($ph, $m[0], $q);
		}
	}
	return $r;
}
}
?>