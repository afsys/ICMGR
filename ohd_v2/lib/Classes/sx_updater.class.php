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
 * sxUpdater - Class for managing files and database updates
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Dev
 */

class sxUpdater
{
	var $db = null;
	var $error_str = null;
	var $updates_opt_table = '#_PREF_updates_options';
	
	var $current_revision = 0;
	var $do_not_show_included_updates = true;
	
	function sxUpdater()
	{
		$this->db =& sxDb::instance();
	}
	
	
	/**
	 * Returns list of updates with statuses
     * @param string  $update_dir      directory with updates
     * @param array   $update_list     list of update for applying
	 */   
	function ApplyUpdates($update_dir, $update_list)
	{
		$update_files = $this->GetUpdatesFilesList($update_dir);
		//var_dump($update_files);
		
		foreach ($update_list as $update_item) {
			//list($update_rev, $update_type) = explode('-', $update_item);
			//$update_type = strtoupper($update_type);
			
			if (!empty($update_files[$update_item])) {
				$itm =& $update_files[$update_item];
				
				// try to apply update
				switch ($itm['type']) {
					case 'SQL':
						$query = file_get_contents($update_dir.$itm['filename']);

						$this->db->exception_on_error = false;
						$is_error = !$this->db->script($query);
						$this->db->exception_on_error = true;
						
						$ids = array (
							'rev_num' => $itm['rev_num'],
							'type'    => $itm['type'],
						);
						
						// check db for current update
						$this->db->q('SELECT rev_num, type, status FROM #_PREF_updates', $ids);
						$res = $this->db->fetchAssoc();
						
						// try to apply update
						if (is_array($res)) {
							if ($res['status'] != 'ok') {
								$this->db->qI('#_PREF_updates', array ('status'  => $is_error ? 'error' : 'ok'), 'UPDATE', $ids);
							}
						}
						else {
							$ids['status'] = $is_error ? 'error' : 'ok';
							$this->db->qI('#_PREF_updates', $ids);
						}
						
						break;
				}
			}

		}
		
		
	}
	
    /**
     * Returns list of updates with statuses
     * @param string  $updates_dir      directory with updates
     */   
	function GetUpdatesList($updates_dir)
	{
		$updates = $this->GetUpdatesFilesList($updates_dir);
		
		// get statuses from database
		$this->db->q('SELECT * FROM #_PREF_updates');
		while ($item = $this->db->fetchAssoc()) {
			$u_id = $item['rev_num'].'-'.strtoupper($item['type']);
			if (isset($updates[$u_id])) {
				$updates[$u_id]['status'] = $item['status'];
			}
			
		}
		
		foreach ($updates as $k=>$update) {
			// remove update
			if ($this->do_not_show_included_updates && $update['rev_num'] <= $this->current_revision) {
				unset($updates[$k]);
				continue;
			}
			
			// set update status
			if (empty($updates[$k]['status'])) {
				if ($update['rev_num'] <= $this->current_revision) $updates[$k]['status'] = 'included';
				else $updates[$k]['status'] = 'not applied';
			}
			else {
				if ($update['rev_num'] <= $this->current_revision) $updates[$k]['status'] = 'included';
			}
			
			
			switch ($updates[$k]['status']) {
				case 'ok':           $updates[$k]['status_html'] = '<span style="color: green; font-weight: bold;">Ok</span>';       break;
				case 'included':     $updates[$k]['status_html'] = '<span style="color: silver; font-weight: bold;">Included</span>'; break;
				case 'not applied':  $updates[$k]['status_html'] = '<span style="color: #D2D200; font-weight: bold;">Not Applied</span>'; break;
				case 'error':        $updates[$k]['status_html'] = '<span style="color: red; font-weight: bold;">Error!</span>'; break;
				default: 
					$updates[$k]['status_html'] = $updates[$k]['status'];
					break;
			}
			
			
		}
		
		uksort($updates, "compare_updates");
		
		/*echo "<pre>";
		var_dump($updates);
		echo "</pre>";*/
				
		return $updates;
	}
	

    /**
     * Returns list of all update files
     * @param string  $updates_dir      directory with updates
     */   
	function GetUpdatesFilesList($updates_dir)
	{
		$updates = array(); 
		
		$d = dir("$updates_dir");
		while (false !== ($entry = $d->read())) {
			if (preg_match('/^update-(.+?) (\d+)(.*?)\.php/', $entry, $match)) {
				$item = array (
					'filename' => $entry,
					'type'     => strtoupper($match[1]),
					'rev_num'  => (int)$match[2],
					'desc'     => trim($match[3]),
				);
				
				$updates[$item['rev_num'].'-'.$item['type']] = $item;
		    }
		}
		$d->close();
		
		return $updates;
	}

	function SetCurrentRevision($rev_num)
	{
		$this->current_revision = $rev_num;
	}
	
	function Run($url_src, $path_dst)
	{
		if ($this->DownloadFile($url_src, 'updatedata.gz') === false) return false;
		return $this->UnzipFiles('updatedata.gz', $path_dst);
	}
	
	function DownloadFile($url, $path)
	{
		$cnt = @file_get_contents($url);
		if ($cnt === false) {
			$this->error_str = 'Could not locate server';
			return false;
		}
		else if (strpos($cnt, 'Error:') === 0) 
		{
			$this->error_str = substr($cnt, 7);
			return false;
		}
		
		// save file
		$handle = fopen($path, 'w');
		fwrite($handle, $cnt);
		fclose($handle);
		return true;
	}
	
	function UnzipFiles($arh_path, $dst)
	{
		$tar = new Archive_Tar($arh_path);
		$cnt = $tar->listContent();
		
		foreach ($cnt as $fitem) {
			$fname = $fitem['filename'];
			//echo "<b>$fname</b><br>";
			//var_dump($fitem);

			// skip dirs and old delete files
			if (file_exists($fname)) {
			 	if (is_dir($fname)) continue;
			 	unlink($fname);
			}
			
			// create full path to file
			$fpath = substr($dst.$fname, 0, strrpos($dst.$fname, '/'));
			mkdirs($fpath);
			
			$handle = fopen(realpath($dst).'/'.$fname, 'w');
			$cnt = $tar->extractInString($fname);
			fwrite($handle, $cnt);
			fclose($handle);
		}
		
		return true;
	}
}



function mkdirs($dir, $mode = 0777, $recursive = true) {
	if (is_null($dir) || $dir === "") return false;
	if (is_dir($dir) || $dir === "/") return true;
	if (mkdirs(dirname($dir), $mode, $recursive)) return mkdir($dir, $mode);
	return false;
}

function compare_updates ($a, $b) {
    list ($a_rev, $a_type) = explode('-', $a);
    list ($b_rev, $b_type) = explode('-', $b);
    
    if ($a_rev == $b_rev) return 0;
    return ($a_rev < $b_rev) ? -1 : 1;
}




?>
