<?
function _requestFopen($url) {
	if ("" == ini_get("allow_url_fopen")) return false;
 	$_str = @file($url);
 	if ($_str == "") return false;
 	$str = join("",$_str);
 	return $str;
}

function _requestSocket($url) {
	if (!function_exists("fsockopen")) return false;
	$data = parse_url($url);
	$fp = @fsockopen($data["host"],80,$errno,$errstr,5);
	if (!$fp) {
		return false;
	}else{
		$crlf = "\r\n";
		$request = "GET ".$data["path"]."?".$data["query"]." HTTP/1.0".$crlf;
		$request .= "Host: ".$data["host"].$crlf;
		$request .= "Connection: close".$crlf;
		$request .= $crlf.$crlf;
		fputs($fp, $request);
		
		$result = "";
		while (!feof($fp)) {
			$result .= fgets($fp, 2048);
		}
		$result = substr($result,strpos($result,$crlf.$crlf));
		return $result;
	}
}

function _requestCurl ($url) {
	if (!function_exists("curl_init")) return false;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_URL, $url); 
	$result = curl_exec($ch);
	return $result;
}

function _requestCurlBinary ($url) {
  if(preg_match('/^Windows/', php_uname())) return false;
  
	$curl_location = "/usr/local/bin/curl";
	if (!@is_file($curl_location)) return false;
	if (!@is_executable($curl_location)) return false;
	if (!function_exists("exec")) return false;
	
	@exec("$curl_location $url", $info);
	$result = implode("",$info);
	return $result;
}

function _requestLynx ($url) {
  if(preg_match('/^Windows/', php_uname())) return false;
  
	$lynx_location = "lynx";
	if (!@is_file($lynx_location)) return false;
	if (!@is_executable($lynx_location)) return false;
	if (!function_exists("exec")) return false;

	@exec("$lynx_location -dump $url", $info);
	$result = implode("",$info);
	return $result;
}

function sendRequest($url) {
	if (false == ($result = _requestFopen($url)))
		if (false == ($result = _requestCurl($url)))
			if (false == ($result = _requestSocket($url)))
				if (false == ($result = _requestCurlBinary($url)))
					if (false == ($result = _requestLynx($url)))
							return false;
	return $result;
}

function oss_http_build_query($formdata, $numeric_prefix = null) {
    // If $formdata is an object, convert it to an array
    if (is_object($formdata)) {
        $formdata = get_object_vars($formdata);
    }

    // Check we have an array to work with
    if (!is_array($formdata)) {
        trigger_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.', E_USER_WARNING);
        return false;
    }

    // If the array is empty, return null
    if (empty($formdata)) {
        return;
    }

    // Argument seperator
    $separator = "&";

    // Start building the query
    $tmp = array ();
    foreach ($formdata as $key => $val) {
        if (is_integer($key) && $numeric_prefix != null) {
            $key = $numeric_prefix . $key;
        }

        if (is_scalar($val)) {
            array_push($tmp, urlencode($key).'='.urlencode($val));
            continue;
        }

        // If the value is an array, recursively parse it
        if (is_array($val)) {
            array_push($tmp, __http_build_query($val, urlencode($key)));
            continue;
        }
    }

    return implode($separator, $tmp);
}
if (!function_exists("__http_build_query"))
{
// Helper function
function __http_build_query ($array, $name)
{
    $tmp = array ();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            array_push($tmp, __http_build_query($value, sprintf('%s[%s]', $name, $key)));
        } elseif (is_scalar($value)) {
            array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
        } elseif (is_object($value)) {
            array_push($tmp, __http_build_query(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
        }
    }

    // Argument seperator
    $separator = "&";

    return implode($separator, $tmp);
}
}
class keyChecker {
	var $host;
	var $response;
	
	function keyChecker($host) {
		$this->host = $host;
	}
	
	function getResponse(){
		return $this->response;
	}
	
  function sendRequest($request) {
  	global $__OSS_version;
  	if (!is_array($request)) return false;
  	$request["domain"] = $_SERVER['HTTP_HOST'];
  	$request["ip"] = $_SERVER["SERVER_ADDR"];
  	$request["rand"] = rand(0,1000);
  	$request["version"] = $__OSS_version;
  	$request["path"] = __FILE__;
  	$query = oss_http_build_query($request);
  	$str = sendRequest($this->host."?".$query);
  	//if ($str == false) return array("error"=>"Failed to connect to license server, please try again!");
  	if ($str == false) return "";
  	$response = unserialize(base64_decode($str));
  	$this->response = $response;
  	return $response;
  }

  function install(){
  	$request = array();
  	$request["action"] = "install";
  	$response = $this->sendRequest($request);

  	if (isset($response["error"])) return $response["error"];
  	else return true;
  }

  function checkKey($key){
  	$request = array();
  	$request["action"] = "check";
  	$request["key"] = $key;
  	$response = $this->sendRequest($request);
		
  	if (isset($response["error"])) return $response["error"];
  	else {
  		if ($response["license_key"] == $key) return true;
  		else {
  			if (isset($response["license_key"])) {
	  			return "You key and server key don't match , or server error";
	  		}else{
	  			//server down?
	  			return true;
	  		}
  		}
  	}
  }
}
?>