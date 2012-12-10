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
 * Error handler module for PHP 4.2.0
 *
 * This class gets page content defined by URL as save it to DB. By page 
 * request it looks for page in DB, or gets it from WEB if it is absent or
 * quite old.
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 28, 2003
 * @version    1.10 Dev
 */
	

/**
 * Determine is class connected to DB or not
 * @var boolean
 */
$old_error_handler = 0;

/**
 * Constructor
 */    
function set_sxErrorHandler()
{
	global $old_error_handler;
	$old_error_handler = set_error_handler("sxErrorHandler");
}  

/*echo '<pre>';
var_dump($_SERVER);*/

/**
 * Error handler
 */  
function sxErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno)) return;
	if ($errno == @E_STRICT) return;

	if ($errno == E_WARNING || $errno == E_NOTICE) { 
		if (!defined('IS_ADMIN')) {
			if ($errno == E_WARNING) return;
			if ($errno == E_NOTICE)  return;
		}
		
		?>
		<div style="left: 1px; font-weight: bold; background: #D2D2D2; color: black; font-family: Verdana, Arial, Helvetica, Sans Serif; font-size: 11px; line-height: 17px;">
			<div style="font-weight: bold; background-color: #FF5555; float: left; ">
				&nbsp;<?= $errno == E_NOTICE ? 'Notice' : 'Warning' ?>&nbsp;
			</div>
			&nbsp;<?= $errstr ?> on <?= $errfile?> : <?= $errline?>
		</div>
		<?php
		return;
	}
	
	switch ($errno) {
		case 1:    $errno_desc = 'E_ERROR';             break;
		case 2:    $errno_desc = 'E_WARNING';           break;
		case 4:    $errno_desc = 'E_PARSE';             break;
		case 8:    $errno_desc = 'E_NOTICE';            break;                       
		case 16:   $errno_desc = 'E_CORE_ERROR';        break;
		case 32:   $errno_desc = 'E_CORE_WARNING';      break;
		case 64:   $errno_desc = 'E_COMPILE_ERROR';     break;
		case 128:  $errno_desc = 'E_COMPILE_WARNING';   break;          
		case 256:  $errno_desc = 'E_USER_ERROR';        break;
		case 512:  $errno_desc = 'E_USER_WARNING';      break;
		case 1024: $errno_desc = 'E_USER_NOTICE';       break;
		case 2047: $errno_desc = 'E_ALL';               break;   
		default:   $errno_desc = 'UNKNOWN';             break;
	}
	
	$err_code = "r". OHD_VERSION_REVISION ."-". strtoupper(md5($errstr.$errfile));
	
	$sx_db_error = (substr($errstr, 0, 16) == 'sxMySQL error ::');
	$debug = debug_backtrace();
	ob_start();
	?>
		<style>
			div.sx_error { font-family: Verdana, Arial, Helvetica, Sans Serif; font-size: 11px; }
		</style>
		<div class="sx_error" style="text-align: left; position: absolute; left: 10px; top: 10px;">
			<div style="background: red; color: white; font-weight: bold; padding: 2px;" 
				onclick="SwitchErrorCnt();" ondblclick="return false;">Error an occured...</div>
			<div id="sx_error_cnt">
				<div id="sx_error_tree">
					<div style="background: #D2D2D2; color: black; padding: 2px; ">
						<b>Details:</b>
							<div style="background: #F3F3F3; color: black; padding: 2px; margin-top: 2px;">
								 <tt><b>&nbsp; - code:</b></tt> <?= $err_code ?>
							</div> 
							<div style="background: #F3F3F3; color: black; padding: 2px;">
								 <tt><b>&nbsp; - type:</b></tt> <?= $errno_desc ?>
							</div>                    
							<div style="background: #F3F3F3; color: black; padding: 2px;">
								 <tt><b>&nbsp; - desc:</b></tt> <?= $errstr ?>
							</div>                    
							<div style="background: #F3F3F3; color: black; padding: 2px;">
								 <tt><b>&nbsp; - file:</b></tt> '<?= $errfile ?>'
							</div>                    
							<div style="background: #F3F3F3; color: black; padding: 2px;">
								 <tt><b>&nbsp; - line:</b></tt> <?= $errline ?>
							</div>                   
					</div>
								
					<div style="background: #D2D2D2; color: black; padding: 2px; ">
						<b>Backtrace:</b>
						<?php foreach ($debug as $k=>$v) 
							if (isset($v['file'])) 
							{ ?>
								<div style="background: #F3F3F3; color: black; padding: 2px;">
									 <tt><?= $v['file'] ." (". $v['line'] .")" ?></tt>
								</div>
							<?php } ?>
					</div>                        
								
					<?php if ($sx_db_error && isset($debug[0]['args'][4]['query'])) { ?>
						<div style="background: #D2D2D2; color: black; padding: 2px; ">
							<b>Query:</b>
								<div style="background: #F3F3F3; color: black; padding: 2px; margin-top: 2px;">
									 <tt><pre><?= $debug[0]['args'][4]['query'] ?></pre></tt>
								</div>
						</div>
					<?php } ?>                        
				</div>
			</div>
		</div>        
		<br />
		<br />                      
		<pre>
	<?
	$dump_cnt = ob_get_contents();
	ob_end_clean();
	
	$dump_cnt = strtr($dump_cnt, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
	?>
	
	<script type="text/javascript">
		function SwitchErrorCnt() {
			var cnt = document.getElementById('sx_error_cnt');
			cnt.style.display = cnt.style.display == 'none' ? '' : 'none';
		}
		
		var div = document.createElement('DIV');
		div.innerHTML = '<?= $dump_cnt ?>';
		
		
		function ShowError() {
			if (!document.body) {
				//document.appendChild(document.createElement('BODY'));
				//alert(document.body);
			}
			document.body.appendChild(div);
		}
		
		if (typeof window.addEventListener != 'undefined') window.addEventListener('load', ShowError, true); 
		else window.attachEvent('onload', ShowError);
	</script>
	
	<?php
	//echo $dump_cnt;

	//var_dump($debug);
	exit(1);    
}

if (isset($local_site)) 
{
	set_sxErrorHandler();
}

?>