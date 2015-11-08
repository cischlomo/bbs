<?php
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL & ~E_NOTICE);
$app_dirname="bbs";
$docroot=$_SERVER['DOCUMENT_ROOT'];
$httproot= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/" . str_replace($docroot,"",$app_dirname);
$cookie_name = 'punbb_cookie';
$cookie_seed = 'theseed';
?>
