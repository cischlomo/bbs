<?php
namespace {
require_once("config.php");
require_once("lib/Router.php");
$jsonarr=json_decode(file_get_contents("php://input"),TRUE);
$db=new Mysqli("localhost","root");
$db->select_db("campidiot");
header ("Content-type: text/json");
} // end global namesp

namespace api {
//this section maps url pattern to function, so e.g. /bbs/api/forum/1 calls "viewforum(1)"
$methodmap=[
 "GET"=>[
  "forum"=>'\api\get\viewforum',
  "topic"=>'\api\get\viewtopic',
  "post"=>'\api\get\reply',
  "nt"=>'\api\get\newtopic',
  "me"=>'\api\get\me_helper',
  "config"=>'\api\get\config',
  "reply" => '\api\get\reply',
  ],
 "POST"=>[
  "forum"=>'\api\post\newtopic',
  "topic"=>'\api\post\replytotopic',
  "post"=>'\api\post\replytopost',
  "login"=>'\api\post\login',
 ],
 "DELETE"=>[
  "post"=>'\api\post\deletepost',
 ]
];

\Router\route($methodmap);
}//end namespace api

namespace api\get{
/******* and these are all the functions that are mapped to distinct url patterns *********/

function reply($pid){
	//exit("reply $pid");
 global $jsonarr,$db;
 $sql="select * from ci_posts where id=$pid";
 $post=$db->query($sql)->fetch_assoc();
 $sql="select * from ci_topics where id=" . $post["topic_id"];
 $post["topic"]=$db->query($sql)->fetch_assoc();
 $sql="select * from ci_forums where id=" . $post["topic"]["forum_id"];
 $post["forum"]=$db->query($sql)->fetch_assoc();
 exit(json_encode($post));
}

function viewtopic ($tid){
 global $jsonarr,$db;
 $sql="select subject,forum_id from ci_topics where id=$tid";
 $topics=$db->query($sql) or die ($db->error);
 if($topics->num_rows!=1) {
  exit(json_encode(array("error"=>"no such topic")));
 }
 $topic=$topics->fetch_assoc();
 $sql="select id as tid,message,replyto,id as pid from ci_posts where topic_id=$tid";
 $posts=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 $sql="select * from ci_forums where id=" . $topic["forum_id"];
 //exit($sql);
 $forum=($db->query($sql)->fetch_assoc());
 $sql="select * from ci_navlinks";
 $forum["navlinks"]=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 //exit(print_r($forum,1));
 exit(json_encode(["tid"=>$tid,"subject"=>$topic["subject"],"forum"=>$forum,"posts"=>$posts]));
}

function viewforum ($fid){
 global $jsonarr, $db;
 $sql="select id from ci_forums where id=$fid";
 if ($db->query($sql)->num_rows==0) {
  exit(json_encode(array("error"=>"no such forum")));
 }
 $sql="select * from ci_forums f where f.id=$fid";
 list($forum)=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 $sql="select * from ci_navlinks";
 $forum["navlinks"]=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 $forum["pagination"]=[];
 $sql="select * from ci_topics where forum_id=$fid";
 $forum["topics"]=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 exit(json_encode($forum));
}
function me_helper(){
 $me=me();
 if ($me["id"]==1){
	 $me["is_guest"]=TRUE;
 }
 exit(json_encode($me));
}
function me() {
 global $jsonarr, $db, $cookie_seed;
 $user_token=urldecode($_REQUEST['user_token']);
 list($userid,$password_hash)=explode(":",$user_token,2);
 $sql="select * from ci_users where id=? and md5(concat(?,password))=?";
 $sth=$db->prepare($sql);
 $sth->bind_param("iss",$userid,$cookie_seed,$password_hash);
 $sth->execute();
 $result = $sth->get_result();
 error_log("numrows: " . $result->num_rows);
 if($result->num_rows==1) {
  return $result->fetch_array(MYSQLI_ASSOC);
 } else { //return guest user
  $sql="select * from ci_users where id=1";
  return $db->query($sql)->fetch_array(MYSQLI_ASSOC);
 }
}

function config(){
	global $db;
	$sql="select * from ci_config";	
	$result=$db->query($sql) or exit(json_encode(["error"=>$db->error]));
	$result=$result->fetch_all(MYSQLI_NUM) or exit(json_encode(["error"=>$db->error]));
	exit(json_encode(array_combine(array_column($result,0),array_column($result,1))));
}

function getpost ($pid){
 global $jsonarr,$db;
 $sql="select * from ci_posts where id=$pid";
 $resp=$db->query($sql)->fetch_assoc();
 if ($resp['id'] > 0){
  //viewtopic($tid);
  $resp['tid']=$resp['topic_id'];
  $resp['pid']=$pid;
  exit(json_encode($resp));
 } else {
  exit(json_encode(["error"=>"no such post"]));
 }
}

} //end namespace api\get

namespace api\post {
	
function deletepost ($pid){
 global $jsonarr,$db;
 error_log ("delete post $pid");
 $sth=$db->prepare("delete from ci_posts where id=?");
 $sth->bind_param("i",$pid);
 $sth->execute() or exit (json_encode(array("error"=>$db->error)));
 exit (json_encode(array("pid"=>$pid)));
}
function replytopost ($pid){
 global $jsonarr,$db;
 $sql="select topic_id as tid from ci_posts where id=$pid";
 list($tid)=$db->query($sql)->fetch_row();
 $sql="insert into ci_posts (topic_id,message,replyto) values (?,?,?)";
 $sth=$db->prepare($sql);
 $sth->bind_param("isi",$tid,$jsonarr["message"],$pid);
 $sth->execute();
 $jsonarr["pid"]=$db->insert_id;
 $jsonarr["tid"]=$tid;
 exit(json_encode($jsonarr));
}


function replytotopic ($tid){
 global $jsonarr,$db;
 $sql="insert into ci_posts (topic_id,message) values (?,?)";
 $sth=$db->prepare($sql);
 $sth->bind_param("is",$tid,$jsonarr["message"]);
 $sth->execute();
 $pid=$db->insert_id;
 $jsonarr["pid"]=$pid;
 $jsonarr["tid"]=$tid;
 exit(json_encode($jsonarr,1));
}
function newtopic($fid){
 global $jsonarr,$db;
 $user=\api\get\me();
 error_log("user " . print_r($user,1));
 $sql="insert into ci_topics (subject,posted,forum_id,owner,last_poster,poster) values (?,?,?,?,?,?)";
 $sth=$db->prepare($sql) or die($db->error);
 $d=time();
 $sth->bind_param("siisss",$jsonarr["subject"],$d,$fid,$user["username"],$user["username"],$user["username"]);
 $sth->execute();
 $tid=$db->insert_id;
 $sql="insert into ci_posts (topic_id,message,poster) values (?,?,?)";
 $sth=$db->prepare($sql) or die($db->error);
 $sth->bind_param("iss",$tid,$jsonarr["message"],$user["username"]);
 $sth->execute();
 exit(json_encode(array("topic_id"=>$tid)));
}


function login(){
 global $jsonarr, $db, $cookie_seed;
 $u=trim($jsonarr["username"]);
 $p=trim($jsonarr["password"]);
 $sql="select * from ci_users where username=? and password=md5(?)";
 $sth=$db->prepare($sql);
 $sth->bind_param("ss",$u,$p);
 $sth->execute();
 $result = $sth->get_result();
 if($result->num_rows==1) {
  exit(json_encode(["userid"=>($result->fetch_assoc())["id"],"password_hash"=>md5($cookie_seed . md5($p))]));
 }
 exit("{}");
}


} //end namespace api\post

?>
