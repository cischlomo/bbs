<?php
function replytotopic ($tid){
 $url="http://xbmc/bbs/api/topic/$tid";
 $response=curlstuff($url);
 header ("Location: /bbs/redir.php?url=http://xbmc/bbs/ui/topic/$tid");
}
function viewtopic ($tid){
 global $jsonobj;
 $resp=json_decode(file_get_contents("http://xbmc/bbs/api/topic/$tid"));
 ?>
 <h1>Topic: <?=$resp->subject?></h1>
 <p>
 <?php foreach ($resp->posts as $post) : ?>
  <?=$post->message?><p>
 <?php endforeach ?>

<h1>Reply</h1>
<form action="/bbs/ui/topic/<?=$tid?>" method="POST">
<textarea name="message"></textarea>
<input type="submit">
</form>
 <?php
}
function newtopic($fid){
 $url="http://xbmc/bbs/api/forum/$fid";
 $response=curlstuff($url);
 print "redirecting to http://xbmc/bbs/ui/topic/$response->topic_id";
}
function curlstuff($url){
//error_log(print_r($_REQUEST,1));
 $curlopts= array( 
        CURLOPT_POST => 1, 
        CURLOPT_HEADER => 0, 
	CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_POSTFIELDS => json_encode($_REQUEST),
	CURLOPT_COOKIE=> $_SERVER['HTTP_COOKIE']

 );
 $ch = curl_init();
 curl_setopt_array($ch,$curlopts);
 return json_decode(curl_exec($ch));
}


require_once("lib/RegexRouter.php");
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);
$router = new RegexRouter();
$prefix="/^\/bbs\/ui";

$router->get( $prefix . '\/forum\/([0-9]+)\/?$/',  "viewforum");
$router->get( $prefix . '\/topic\/([0-9]+)\/?$/',  "viewtopic");
$router->get( $prefix . '\/post\/([0-9]+)\/?$/',   "getpost");
$router->get( $prefix . '\/nt\/([0-9]+)\/?$/',   "newtopicform");

$router->post( $prefix . '\/forum\/([0-9]+)\/?$/',  "newtopic");
$router->post( $prefix . '\/topic\/([0-9]+)\/?$/', "replytotopic");
$router->post( $prefix . '\/post\/([0-9]+)\/?$/',  "replytopost");
 
function newtopicform ($fid) {
 ?>
   <h1>post new topic</h1>
<form action="/bbs/ui/forum/<?=$fid?>" method="POST">
subject<input type="text" name="subject"><br>
message<input type="text" name="message"><br>
<input type="submit">
</form>

 <?php 
}
function viewforum ($fid){
 $topics=json_decode(file_get_contents("http://xbmc/bbs/api/forum/$fid"));
 print "<h1>Topics</h1>\n";
 foreach ($topics as $topic){
  print "<h4>$topic->subject</h4>\n";
  print "<h4>by: $topic->poster</h4>\n";
 }
}
function getpost ($pid){
 global $jsonobj;
 print "read post# $pid";
}

function replytopost ($pid){
 global $jsonobj;
 print "reply to post $pid<p>";
 print "message: " . $jsonobj->message;
}

#$jsonobj=json_decode(file_get_contents("php://input"));
$router->execute($_SERVER['REQUEST_URI']);


?>
