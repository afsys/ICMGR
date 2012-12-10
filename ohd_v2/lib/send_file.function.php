<?php 
	
function SendFile($filename, $fname) 
{ 
   //die("$filename - $fname");
   
   session_cache_limiter('public'); 

   if (filesize($filename)<=0) 
   { 
       header ("HTTP/1.0 404 Not Found"); 
       return false; 
   } 
   $fsize = filesize($filename); 
   $ftime = date("D, d M Y H:i:s T", filemtime($filename)); 
   $fd = fopen($filename, "rb"); 
   if (!$fd)
   { 
       header ("HTTP/1.0 403 Forbidden"); 
       return false; 
   } 

   if (isset($_SERVER["HTTP_RANGE"])) 
   { 
       $range = $_SERVER["HTTP_RANGE"]; 
       $range = str_replace("bytes=", "", $range); 
       $range = str_replace("-", "", $range); 
       if ($range) 
       { 
           fseek($fd, $range); 
       } 
   } 
   if (isset($range)) 
   { 
       header("HTTP/1.1 206 Partial Content"); 
   } 
   else 
   { 
       header("HTTP/1.1 200 OK"); 
       $range = 0; 
   } 

   header("Content-Disposition: inline;filename=".$fname);  // .";size=".($fsize-$range)
   header("Last-Modified: ".$ftime); 
   header("Accept-Ranges: bytes"); 
   header("Content-Length: ".($fsize-$range)); 
   header("Content-Range: bytes $range-".($fsize -1)."/".$fsize); 
   header("Content-Type: application/octet-stream"); 
   while (!feof($fd)) echo fread($fd, 4096); 
   fclose($fd); 
   
   return true; 
} 

/*
sendfilo("1.mp3","super.mp3"); 

function SendFile($filename, $fname) 
{
	if (filesize($filename) <= 0) 
	{
		header ("HTTP/1.0 404 Not Found");
		return false;
	}
	
	$fsize = filesize($filename);
	$ftime = date("D, d M Y H:i:s T", filemtime($filename));
	$fd = fopen($filename, "rb");
	if (!$fd)
	{ 
		header ("HTTP/1.0 403 Forbidden");
		return false;
	}

	// если запрашивающий агент поддерживает докачку 
	if (isset($_SERVER["HTTP_RANGE"])) 
	{ 
		$range = $_SERVER["HTTP_RANGE"];
		$range = str_replace("bytes=", "", $range);
		$range = str_replace("-", "", $range);
		if ($range) 
		{
			fseek($fd, $range);
		}
	}
	
	if (isset($range)) 
	{ 
		header("HTTP/1.1 206 Partial Content");
	} else 
	{ 
		header("HTTP/1.1 200 OK");
		$range=0;
	}

	header("Content-Disposition: attachment;");
	header("Last-Modified: ".$ftime);
	header("Accept-Ranges: bytes");
	header("Content-Length: ".($fsize-$range));
	header("Content-Range: bytes $range-".($fsize -1)."/".$fsize);
	header("Content-Type: application/octet-stream");
	while (!feof($fd)) 
	{
		$content = fread($fd, 4096);
		print $content;
	}
	fclose($fd);
	return true;
}
*/




?>