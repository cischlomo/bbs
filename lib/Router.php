<?php
namespace Router;
function route($methodmap) {
	$method=$_SERVER["REQUEST_METHOD"];
	$pathinfo=$_SERVER["PATH_INFO"];
	$pattern="#[^/]+#";
	
	$pattern="#/(.*?)/?([0-9]*)$#"; // method name followed by 0 or 1 numeric arguments
	preg_match($pattern,$pathinfo,$m);
	//exit("<pre>".print_r($m,1));
	//one or more numeric arguments at the end
	$route=$m[1];
	$arg  =$m[2];
	 
	//exit($methodmap[$method][$route]);
	
	if(isset($methodmap[$method][$route])){
		call_user_func ($methodmap[$method][$route] , $arg);
	} else {
		exit("no such route $route");
	}
}

?>
