<?php
namespace {
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
$smarty->registerPlugin("function",'pagination','pagination');
$smarty->registerPlugin("function",'format_time','format_time');
$user=\Utility\api("/api/me");
//exit("<pre>$user");
$pun_config=\Utility\api("/api/config");

function pagination ($params, $smarty){
 global $user;
 $cur_forum=$params["forum"];
 $p=(!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
 $num_pages = ceil($cur_forum["num_topics"] / $user["disp_topics"]);
 return  \ui\paginate($num_pages, $p, "blah");
}

} //end global namespace

namespace ui {
//this section maps url pattern to function, so e.g. /bbs/api/forum/1 calls "viewforum(1)"
$methodmap=[ 
 'GET'=>[
  'forum'=>'\ui\get\viewforum',
  'topic'=>'\ui\get\viewtopic',
  'post'=>'\ui\get\getpost',
  'reply'=>'\ui\get\reply',
  'new_topic'=>'\ui\get\newtopic',
  'login'=>'\ui\get\login',
  'logout'=>'\ui\get\logout',
  'me'=>'\ui\get\me',
  'reply/post' => '\ui\get\replytopost',
  'reply/topic' => '\ui\get\replytotopic',
  ],
 'POST'=>[
  'forum'=>'\ui\post\newtopic',
  'topic'=>'\ui\post\replytotopic',
  'post'=>'\ui\post\replytopost',
  'login'=>'\ui\post\login',
  ],
];
\Router\route($methodmap);



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

}//end namespace ui

//begin namespace ui\get
namespace ui\get {
/******* and these are all the functions that are mapped to distinct url patterns *********/
function replytopost ($pid){
 global $httproot;
 exit("reply to post $pid<p>");
}

function replytotopic($tid){
	
	exit("reply to topic $tid");
}

function reply ($pid){
	//exit($pid);
	global  $httproot, $cookie_name, $user, $smarty, $pun_config;
	$post=\Utility\api("/api/reply/$pid");
	//exit("<pre>". print_r($post,1));
	$smarty->assign("user",$user);
	$smarty->assign("post",$post);
	$smarty->display("reply.tpl");
}

function viewtopic ($tid){
 global  $cookie_name, $user, $smarty, $pun_config;
 $topic=\Utility\api("/api/topic/$tid");
 
 if (isset($topic["error"])){
  exit($topic["error"]);
 }
  //exit("<pre>".print_r($topic["posts"][0],1));

 $smarty->assign("topic",$topic);
 $smarty->assign("user",$user);
 $smarty->display("viewtopic.tpl");
}
function viewforum ($fid){
 global  $cookie_name, $user, $smarty, $pun_config;
 //exit("<pre>" . print_r($pun_config,1));
 $forum=\Utility\api("/api/forum/$fid");
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

function getpost ($pid){
 global $httproot;
 $response=\Utility\api("/api/post/$pid"); //check if post exists
 if (isset($response["error"])){
  exit($response["error"]);
 }
 if (isset($_GET['action']) && $_GET['action']=="delete" && $_GET['confirm']=='y'){
  $result=\Utility\api( "/api/post/$pid",\Utility\DELETE);
  if (isset($result["error"])) {
   exit ("error: " . $result["error"]);
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


function logout () {
 global $cookie_name;
 setcookie($cookie_name, null, -1, '/');
 exit ("you are logged out");
}
function me(){
	exit("<pre>" . print_r(\Utility\api("/api/me"),1));
}


function newtopic ($fid) {
 ?>
   <h1>post new topic</h1>
<form action="/bbs/ui/forum/<?=$fid?>" method="POST">
subject<input type="text" name="subject"><br>
message<input type="text" name="message"><br>
<input type="submit">
</form>

 <?php 
}

} //end namespace ui\get


//begin namespace ui\post
namespace ui\post {

function replytopost ($pid){
 global $httproot;
 $post=\Utility\api("/api/post/$pid",\Utility\POST);
 getpost($post["pid"]);
}

function replytotopic ($tid){
 global $httproot;
 $url= "/api/topic/$tid";
 $topic=\Utility\api($url,\Utility\POST);
 header ("Location: $httproot/ui/topic/$tid%23" . $topic["pid"]);
}

function newtopic($fid){
 global $httproot;
 //exit($fid);
 $response=\Utility\api("/api/forum/$fid",\Utility\POST);
 header ("Location: $httproot/ui/topic/" . $response["topic_id"]);
}

function login () {
 global $cookie_name,$user;
 $url= "/api/login";
 $response=\Utility\api($url,\Utility\POST);
 //exit("<pre>".print_r($response,1));
 if (!isset($response["userid"])) {
  exit ("login failed");
 }
 setcookie($cookie_name, $response["userid"] . ":" . $response["password_hash"] ,0,"/");
 header("Location: /bbs/ui/login");
 exit;
}

}//end namespace ui\post

?>
