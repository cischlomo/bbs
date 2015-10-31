<?php
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);
$pwd=getenv("PWD");
$docroot=$_SERVER['DOCUMENT_ROOT'];
$httproot= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . str_replace($docroot,"",$pwd);


?>
