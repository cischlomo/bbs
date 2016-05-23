<?php
function route($methodmap) {
 $method=$_SERVER["REQUEST_METHOD"];
 $pathinfo=$_SERVER["PATH_INFO"];
 preg_match_all("#[^/]+#",$pathinfo,$m);
 call_user_func ($methodmap[$method][$m[0][0]] , $m[0][1]);
}

?>
