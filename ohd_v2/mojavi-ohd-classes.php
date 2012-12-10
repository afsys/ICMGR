<?php
	
require_once 'PEAR/JSON.php';
	
class AjaxAction extends Action
{
	function & AjaxAction ()
	{
		$this->db   =& sxDb::instance();
		$this->json = new Services_JSON;
		$this->res = array (
			'errorCode'    => 0,
			'errorMessage' => 'Ok!'
		);
		
		header("Content-type: text/javascript");
	}
   
	function execute (&$controller, &$request, &$user)
	{
		$this->controller =& $controller;
		$this->request    =& $request;
		$this->user       =& $user;
		
		$func   = $request->getParameter('func');
		$params = $request->getParameter('params');
		if (!empty($params)) $params = $this->json->decode($params);
		if ($func && method_exists($this, $func)) $this->$func($params);
	}  
	
	function setResError($message, $code = 1)
	{
		$this->res['errorCode']    = $code;
		$this->res['errorMessage'] = $message;
	}
	
	function isSecure()
	{
		return false;   
	}
	
	function writeRes()
	{
		header("X-JSON: ".$this->json->encode($this->res));
	}
}
	
?>