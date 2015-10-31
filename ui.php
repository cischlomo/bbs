<?php
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);
require_once("lib/RegexRouter.php");

function replytopost ($pid){
 print "reply to post $pid<p>";
 print "message: " . $_REQUEST['message'];
}

function replypostform ($pid){
 ?>
 <h1>Reply to post</h1>
 <form action="/bbs/ui/post/<?=$pid?>" method="POST">
  message<input type="text" name="message"><br>
  <input type="submit">
 </form>
 <?php
}
function viewtopic ($tid){
 $url="http://xbmc/bbs/api/topic/$tid";
 $resp=json_decode(file_get_contents($url));
 if (isset($resp->error)){
  exit($resp->error);
 }
 ?>
 <h1>Topic: <?= $resp->subject ?></h1>
 <p>
 <?php foreach ($resp->posts as $post) : ?>
  <a name="#<?= $post->pid ?>"></a>
  <?= $post->message ?><a href="http://xbmc/bbs/ui/post/reply/<?= $post->pid ?>">Reply</a>
  <p>
 <?php endforeach ?>

<h1>Reply</h1>
<form action="/bbs/ui/topic/<?=$tid?>" method="POST">
<textarea name="message"></textarea>
<input type="submit">
</form>
 <?php
}
function getpost ($pid){
 $url="http://xbmc/bbs/api/post/$pid";
 $response=json_decode(file_get_contents($url));
 header("Location: http://xbmc/bbs/ui/topic/" . $response->tid . "#" . $response->pid);
}

$router = new RegexRouter(array(
 "prefix"=>"/^\/bbs\/ui",
 "get"=>array(
  "forum"=>"viewforum",
  "topic"=>"viewtopic",
  "post"=>"getpost",
  "post\/reply\/form"=>"replypostform",
  "nt"=>"newtopicform",
  ),
 "post"=>array(
  "forum"=>"newtopic",
  "topic"=>"replytotopic",
  "post"=>"replytopost"
  )
 ));

function replytotopic ($tid){
 $url="http://xbmc/bbs/api/topic/$tid";
 $response=curlstuff($url);
 header ("Location: /bbs/redir.php?url=http://xbmc/bbs/ui/topic/$tid%23$response->pid");
}
function newtopic($fid){
 $url="http://xbmc/bbs/api/forum/$fid";
 $response=curlstuff($url);
 header ("Location: /bbs/redir.php?url=http://xbmc/bbs/ui/topic/$response->topic_id");
 //print "redirecting to http://xbmc/bbs/ui/topic/$response->topic_id";
}

 
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
#$jsonobj=json_decode(file_get_contents("php://input"));
$router->execute($_SERVER['REQUEST_URI']);

function curlstuff($url){
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


?>
