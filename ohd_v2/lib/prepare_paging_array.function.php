<?php

// $cur_page - current page
// $pages_count - total pages count
// $max_set_width - maximal width or set widht (initial, midle and final): 1 2 3 4 5 ..... 10 11 12 13 14 ..... 20 30 31 32 33 for 5

function PreparePagingArray($cur_page, $pages_count, $max_set_width)
{
	$res_array = array();
	if ($pages_count <= $max_set_width*2) 
	{
		for ($i = 1; $i <= $pages_count; $i++) $res_array[] = $i;
	}
	else
	{
		for ($i = 1; $i <= $max_set_width; $i++) $res_array[] = $i;
		$res_array[] = null;
		for ($i = $pages_count-$max_set_width+1; $i <= $pages_count; $i++) $res_array[] = $i;
	}
	
	return $res_array;
}

?>