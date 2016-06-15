<?php
namespace Router;
function route($methodmap) {
	$method=$_SERVER["REQUEST_METHOD"];
	$pathinfo=$_SERVER["PATH_INFO"];
	preg_match_all("#[^/]+#",$pathinfo,$m);
	//exit("<pre>".print_r($m,1));
	if(isset($methodmap[$method][$m[0][0]])){
		call_user_func ($methodmap[$method][$m[0][0]] , $m[0][1]);
	} else {
		exit("no such func");
	}
}

?>
