<?php
require_once("config.php");
require_once("lib/RegexRouter.php");
$user=getuser();

function getuser(){
 global $httproot, $cookie_name;
 $url=$httproot . "api/me";
 if(isset($_COOKIE[$cookie_name])){
  $_REQUEST=array_merge($_REQUEST,unserialize($_COOKIE[$cookie_name]));
 }
 if(!isset($_REQUEST['password_hash']) && isset ($_REQUEST['password'])){
  $_REQUEST['password_hash']=$_REQUEST['password'];
 }
 return curlstuff($url,1); 
}

//this section maps url pattern to function, so e.g. /bbs/api/forum/1 calls "viewforum(1)"
$router = new RegexRouter(array(
 "prefix"=>"/^\/bbs\/ui",
 "get"=>array(
  "forum"=>"viewforum",
  "topic"=>"viewtopic",
  "post"=>"getpost",
  "post\/reply\/form"=>"replypostform",
  "topic\/form"=>"newtopicform",
  "login"=>"login",
  "logout"=>"logout",
  ),
 "post"=>array(
  "forum"=>"newtopic",
  "topic"=>"replytotopic",
  "post"=>"replytopost",
  "login"=>"loginpost",
  )
 ));
$router->execute($_SERVER['REQUEST_URI']);

/******* and these are all the functions that are mapped to distinct url patterns *********/
function viewforum ($fid){
 global $httproot, $cookie_name;
 $topics=curlstuff($httproot . "/api/forum/$fid");
 if (isset($topics->error)){
  exit($topics->error);
 }
 print "<a href='/bbs/ui/topic/form/$fid'>new topic</a><p>";
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
 $response=curlstuff($url,1);
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
function viewtopic ($tid){
 global $httproot;
 $url=$httproot . "/api/topic/$tid";
 $resp=curlstuff($url);
 if (isset($resp->error)){
  exit($resp->error);
 }
 ?>
 <h1>Topic: <?= $resp->subject ?></h1>
 <p>
 <?php foreach ($resp->posts as $post) : ?>
  <a name="#<?= $post->pid ?>"></a>
  <?= $post->message ?><a href="/bbs/ui/post/reply/form/<?= $post->pid ?>">Reply</a>
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
 global $httproot;
 $url=$httproot . "/api/post/$pid";
 $response=curlstuff($url);
 if (isset($response->error)){
  exit($response->error);
 }
 header("Location: $httproot/ui/topic/" . $response->tid . "#" . $response->pid);
}

function replytotopic ($tid){
 global $httproot;
 $url=$httproot . "/api/topic/$tid";
 $response=curlstuff($url,1);
 header ("Location: /bbs/redir.php?url=$httproot/ui/topic/$tid%23$response->pid");
}
function newtopic($fid){
 global $httproot;
 $url=$httproot . "/api/forum/$fid";
 $response=curlstuff($url);
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
 global $httproot,$cookie_name;
 $requested_user=$_REQUEST['username'];
 $user=getuser();
 error_log("comparing " . $user->username . " with $requested_user");
 if (isset($user->username)) {
  if ($user->username !== $requested_user){
   exit ("login failed");
  }
 } else {
  exit ("login failed");
 }

 setcookie($cookie_name,serialize(
  array("username"=>$user->username,
        "password_hash"=>$user->password)
  ),0,"/");
 exit ("hello " . $user->username);
}

function logout () {
 global $cookie_name;
 setcookie($cookie_name, null, -1, '/');
 exit ("you are logged out");
}

/*** curlstuff isn't mapped to a url its just a utility function to call the api with *****/
function curlstuff($url, $post=0){
 $curlopts= array( 
	CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HEADER => 0, 
	CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url
 );
 if ($post>0){
  $curlopts[CURLOPT_POST] = 1;
  if (isset($_REQUEST)){
   $curlopts[CURLOPT_POSTFIELDS] = json_encode($_REQUEST);
  }
 }
 $ch = curl_init();
 curl_setopt_array($ch,$curlopts);
 $output= json_decode(curl_exec($ch));
 //error_log(curl_error($ch));
 curl_close($ch);
 return $output;
}


?>
