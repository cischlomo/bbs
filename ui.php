<?php
require_once("config.php");
require_once("lib/Router.php");
require_once("lib/utility.php");
$user=Utility::getuser();
//exit("user: " . print_r($user,1));
//this section maps url pattern to function, so e.g. /bbs/api/forum/1 calls "viewforum(1)"
$methodmap=[ 
 "GET"=>[
  "forum"=>"viewforum",
  "topic"=>"gettopic",
  "post"=>"getpost",
  "post\/reply\/form"=>"replypostform",
  "new_topic"=>"newtopicform",
  "login"=>"login",
  "logout"=>"logout",
  ],
 "POST"=>[
  "forum"=>"newtopic",
  "topic"=>"replytotopic",
  "post"=>"replytopost",
  "login"=>"loginpost",
  ],
];
route($methodmap);

/******* and these are all the functions that are mapped to distinct url patterns *********/
function viewforum ($fid){
 global $httproot, $cookie_name, $user;
 $topics=Utility::curlstuff($httproot . "/api/forum/$fid");
 if (isset($topics->error)){
  exit($topics->error);
 }
 if($user) {
  print "hello, $user->username<p>";
 } else {
  print "<a href=\"/bbs/ui/login\">login</a><p>";
 }
 print "<a href='/bbs/ui/new_topic/$fid'>new topic</a><p>";
 print "<h1>Topics</h1>\n";
 foreach ($topics as $topic){
  print "<a href=\"/bbs/ui/topic/$topic->id\">$topic->subject</a>\n";
  print "<h6>by: $topic->poster</h6>\n";
 }
}
function replytopost ($pid){
 global $httproot;
 print "reply to post $pid<p>";
 $url= $httproot . "/api/post/$pid";
 $response=Utility::curlstuff($url,Utility::POST);
 getpost($response->pid);
 //exit(print_r($response,1));
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
function gettopic ($tid){
 global $httproot;
 $url=$httproot . "/api/topic/$tid";
 $resp=Utility::curlstuff($url);
 if (isset($resp->error)){
  exit($resp->error);
 }
 ?>
 <h1>Topic: <?= $resp->subject ?></h1>
 <p>
 <?php foreach ($resp->posts as $post) : ?>
  <a name="#<?= $post->pid ?>"></a>
  <?= $post->message ?>
  <p>
  <?php foreach ($post->links as $k => $v) : ?>
   <a href="<?= $v ?>"><?= $k ?></a> |
  <?php endforeach ?>

 <?php endforeach ?>

<h1>Reply</h1>
<form action="/bbs/ui/topic/<?=$tid?>" method="POST">
<textarea name="message"></textarea>
<input type="submit">
</form>
 <?php
}
function getpost ($pid){
 //exit($pid);
 global $httproot;
 $url=$httproot . "/api/post/$pid";
 $response=Utility::curlstuff($url); //check if post exists
 if (isset($response->error)){
  exit($response->error);
 }
 if (isset($_GET['action']) && $_GET['action']=="delete" && $_GET['confirm']=='y'){
  $result=Utility::curlstuff($httproot . "/api/post/$pid",Utility::DELETE);
  if (isset($result->error)) {
   exit ("error: " . $result->error);
  }
  exit ("deleted post " . $pid);
 }
 if (isset($_GET['action']) && $_GET['action']=="delete"){
?>
 are you sure you want to delete post <?= $pid ?> <a href="?action=delete&confirm=y"=>confirm</a>
 <?php
 exit();
 }
 header("Location: $httproot/ui/topic/" . $response->tid . "#" . $response->pid);
}

function replytotopic ($tid){
 global $httproot;
 $url=$httproot . "/api/topic/$tid";
 $response=Utility::curlstuff($url,Utility::POST);
 header ("Location: /bbs/redir.php?url=$httproot/ui/topic/$tid%23$response->pid");
}
function newtopic($fid){
 global $httproot;
 $url=$httproot . "/api/forum/$fid";
 $response=Utility::curlstuff($url,Utility::POST);
 header ("Location: /bbs/redir.php?url=$httproot/ui/topic/$response->topic_id");
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

function login () {
 global $user;
 if($user->id>1){
  exit("you have to logout first");
 }
 ?>
 <form action="/bbs/ui/login" method="post">
 username <input name="username"><br>
 password <input name="password" type="password">
 <input type="submit">
 </form>
 <?php
}
function loginpost () {
 global $httproot,$cookie_name,$user;
 $url=$httproot . "/api/login";
 $response=Utility::curlstuff($url,Utility::POST);
 if (!isset($response->user_token)) {
  exit ("login failed");
 }
 setcookie($cookie_name,serialize(
  array("user_token"=>$response->user_token)
  ),0,"/");
 exit ("hello " . $user->username);
}

function logout () {
 global $cookie_name;
 setcookie($cookie_name, null, -1, '/');
 exit ("you are logged out");
}

?>
