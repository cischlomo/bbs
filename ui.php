<?php
ini_set("display_errors","on");
error_reporting(E_ALL);

include_once("config.php");
include_once("lib/Router.php");
include_once("lib/utility.php");
include_once("lib/pun.php");
include 'smarty/Smarty.class.php';
$smarty = new Smarty;
$smarty->error_reporting = E_ALL & ~E_NOTICE;
$smarty->caching = false;
$smarty->registerPlugin("function","pagination","pagination");
$smarty->registerPlugin("function","format_time","format_time");
$user=Utility\api("/api/me");
//exit("<pre>$user");
$pun_config=Utility\api("/api/config");
//exit("<pre>" . print_r($pun_config,1));
//this section maps url pattern to function, so e.g. /bbs/api/forum/1 calls "viewforum(1)"
$methodmap=[ 
 "GET"=>[
  "forum"=>"viewforum",
  "topic"=>"viewtopic",
  "post"=>"getpost",
  "reply"=>"replypostform",
  "new_topic"=>"newtopicform",
  "login"=>"login",
  "logout"=>"logout",
  "me"=>"me"
  ],
 "POST"=>[
  "forum"=>"newtopic",
  "topic"=>"replytotopic",
  "post"=>"replytopost",
  "login"=>"loginpost",
  ],
];
Router\route($methodmap);

/******* and these are all the functions that are mapped to distinct url patterns *********/
function viewtopic ($tid){
 global  $cookie_name, $user, $smarty, $pun_config;
 $topic=Utility\api("/api/topic/$tid");
 
 if (isset($topic["error"])){
  exit($topic["error"]);
 }
  //exit("<pre>".print_r($topic["posts"][0],1));

 $smarty->assign("topic",$topic);
 $smarty->assign("user",$user);
 $smarty->assign("forum",$topic["forum"]);
 $smarty->display("viewtopic.tpl");
}
function viewforum ($fid){
 global  $cookie_name, $user, $smarty, $pun_config;
 //exit("<pre>" . print_r($pun_config,1));
 $forum=Utility\api("/api/forum/$fid");
 $smarty->assign("user",$user);
 if ($user["is_guest"]){
	 $forum["navlinks"][]=["link_url"=>"/bbs/ui/login","link_text"=>"Login"];
 } else {
	 $forum["navlinks"][]=["link_url"=>"/bbs/ui/logout","link_text"=>"Logout"];
	 //exit("<pre>".print_r($forum["navlinks"],1));
 }
 $smarty->assign("forum",$forum);
 $smarty->display("viewforum.tpl");
}

function replytopost ($pid){
 print "reply to post $pid<p>";
 $url=  "/api/post/$pid";
 $response=Utility\api($url,Utility\POST);
 getpost($response->pid);
 //exit(print_r($response,1));
}

function replypostform ($pid){
	//exit($pid);
	global  $httproot, $cookie_name, $user, $smarty, $pun_config;
	 $smarty->display("reply.tpl");
}

function getpost ($pid){
 global $httproot;
 
 $url= "/api/post/$pid";
 $response=Utility\api($url); //check if post exists
 if (isset($response->error)){
  exit($response->error);
 }
 if (isset($_GET['action']) && $_GET['action']=="delete" && $_GET['confirm']=='y'){
  $result=Utility\api( "/api/post/$pid",Utility\DELETE);
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
 header("Location: $httproot/ui/topic/" . $response["tid"] . "#" . $response["pid"]);
}

function replytotopic ($tid){
 global $httproot;
 $url= "/api/topic/$tid";
 $response=Utility\api($url,Utility\POST);
 header ("Location: $httproot/ui/topic/$tid%23" . $response["pid"]);
}
function newtopic($fid){
 global $httproot;
 $url= "/api/forum/$fid";
 $response=Utility\api($url,Utility\POST);
 header ("Location: $httproot/ui/topic/" . $response["topic_id"]);
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
 //exit("<pre>hi".print_r($user,1));
 if($user["id"]>1){
  exit("hello " . $user["username"]);
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
 global $cookie_name,$user;
 $url= "/api/login";
 $response=Utility\api($url,Utility\POST);
 //exit("<pre>".print_r($response,1));
 if (!isset($response["userid"])) {
  exit ("login failed");
 }
 setcookie($cookie_name, $response["userid"] . ":" . $response["password_hash"] ,0,"/");
 header("Location: /bbs/ui/login");
 exit;
}

function logout () {
 global $cookie_name;
 setcookie($cookie_name, null, -1, '/');
 exit ("you are logged out");
}
function me(){
	exit("<pre>" . print_r(Utility\api("/api/me"),1));
}
function pagination ($params, $smarty){
 global $user;
 $cur_forum=$params["forum"];
 $p=(!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
 $num_pages = ceil($cur_forum["num_topics"] / $user["disp_topics"]);
 return  paginate($num_pages, $p, "blah");
}
function paginate($num_pages, $cur_page, $link_to)
{
	$pages = array();
	$link_to_all = false;

	// If $cur_page == -1, we link to all pages (used in viewforum.php)
	if ($cur_page == -1)
	{
		$cur_page = 1;
		$link_to_all = true;
	}

	if ($num_pages <= 1)
		$pages = array('<strong>1</strong>');
	else
	{
		if ($cur_page > 3)
		{
			$pages[] = '<a href="'.$link_to.'&amp;p=1">1</a>';

			if ($cur_page != 4)
				$pages[] = '&hellip;';
		}

		// Don't ask me how the following works. It just does, OK? :-)
		for ($current = $cur_page - 2, $stop = $cur_page + 3; $current < $stop; ++$current)
		{
			if ($current < 1 || $current > $num_pages)
				continue;
			else if ($current != $cur_page || $link_to_all)
				$pages[] = '<a href="'.$link_to.'&amp;p='.$current.'">'.$current.'</a>';
			else
				$pages[] = '<strong>'.$current.'</strong>';
		}

		if ($cur_page <= ($num_pages-3))
		{
			if ($cur_page != ($num_pages-3))
				$pages[] = '&hellip;';

			$pages[] = '<a href="'.$link_to.'&amp;p='.$num_pages.'">'.$num_pages.'</a>';
		}
	}

	return implode('&nbsp;', $pages);
}
function get_remote_address() {
	return $_SERVER["REMOTE_ADDR"];
}
?>
