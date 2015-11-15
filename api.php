<?php
require_once("config.php");
require_once('lib/RegexRouter.php');
$jsonobj=json_decode(file_get_contents("php://input"));
$db=new Mysqli("localhost","root");
$db->select_db("campidiot");

//this section maps url pattern to function, so e.g. /bbs/api/forum/1 calls "viewforum(1)"
$router = new RegexRouter(array(
 "prefix"=>"/^\/bbs\/api",
 "get"=>array(
  "forum"=>"viewforum",
  "topic"=>"viewtopic",
  "post"=>"getpost",
  "nt"=>"newtopicform",
  "me"=>"me_helper",
  ),
 "post"=>array(
  "forum"=>"newtopic",
  "topic"=>"replytotopic",
  "post"=>"replytopost",
  "login"=>"login",
 ),
 "delete"=>array(
  "post"=>"deletepost",
  )
 ));
$router->execute($_SERVER['REQUEST_URI']);
 
/******* and these are all the functions that are mapped to distinct url patterns *********/
function deletepost ($pid){
 global $jsonobj,$db;
 error_log ("delete post $pid");
 $sth=$db->prepare("delete from ci_posts where id=?");
 $sth->bind_param("i",$pid);
 $sth->execute();
 if ($db->errno) {
  exit (json_encode(array("error"=>$db->error)));
 }
 exit (json_encode(array("pid"=>$pid)));
}
function replytopost ($pid){
 global $jsonobj,$db;
 $sql="select topic_id as tid from ci_posts where id=$pid";
 list($tid)=$db->query($sql)->fetch_row();
 $sql="insert into ci_posts (topic_id,message,replyto) values (?,?,?)";
 $sth=$db->prepare($sql);
 $sth->bind_param("isi",$tid,$jsonobj->message,$pid);
 $sth->execute();
 $jsonobj->pid=$db->insert_id;
 $jsonobj->tid=$tid;
 exit(json_encode($jsonobj));
}

function viewtopic ($tid){
 global $jsonobj,$db;
 $sql="select subject from ci_topics where id=$tid";
 $result=$db->query($sql) or die ($db->error);
 list($subject)=$result->fetch_row();
 if($subject===NULL) {
  exit(json_encode(array("error"=>"no such topic")));
 }
 $sql="select id as tid,message,replyto,id as pid from ci_posts where topic_id=$tid";
 $resp=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 $output=array();
 foreach ($resp as $row){
  $row["links"]=array("delete"=>"/bbs/ui/post/".$row["pid"]."?action=delete");
  $output[]=$row;
 }
 exit(json_encode(array("tid"=>$tid,"subject"=>$subject,"posts"=>$output)));
}

function getpost ($pid){
 global $jsonobj,$db;
 $sql="select * from ci_posts where id=$pid";
 $resp=$db->query($sql)->fetch_assoc();
 if ($resp['id'] > 0){
  //viewtopic($tid);
  $resp['tid']=$resp['topic_id'];
  $resp['pid']=$pid;
  exit(json_encode($resp));
 } else {
  exit(json_encode(array("error"=>"no such post")));
 }
}
function replytotopic ($tid){
 global $jsonobj,$db;
 $sql="insert into ci_posts (topic_id,message) values (?,?)";
 $sth=$db->prepare($sql);
 $sth->bind_param("is",$tid,$jsonobj->message);
 $sth->execute();
 $pid=$db->insert_id;
 $jsonobj->pid=$pid;
 $jsonobj->tid=$tid;
 exit(json_encode($jsonobj,1));
}
function newtopic($fid){
 global $jsonobj,$db;
 $user=me();
 error_log("user " . print_r($user,1));
 $sql="insert into ci_topics (subject,posted,forum_id) values (?,?,?)";
 $sth=$db->prepare($sql) or die($db->error);
 $d=time();
 $sth->bind_param("sii",$jsonobj->subject,$d,$fid);
 $sth->execute();
 $tid=$db->insert_id;
 $sql="insert into ci_posts (topic_id,message) values (?,?)";
 $sth=$db->prepare($sql) or die($db->error);
 $sth->bind_param("is",$tid,$jsonobj->message);
 $sth->execute();
 exit(json_encode(array("topic_id"=>$tid)));
}
function viewforum ($fid){
 global $jsonobj, $db;
 $sql="select id from ci_forums where id=$fid";
 if ($db->query($sql)->num_rows==0) {
  exit(json_encode(array("error"=>"no such forum")));
 }
 $sql="select * from ci_topics where forum_id=$fid";
 exit(json_encode(($db->query($sql))->fetch_all(MYSQLI_ASSOC)));
}

function me_helper(){
 exit(json_encode(me()));
}
function me() {
 global $jsonobj, $db, $cookie_seed;
 $user_token=urldecode($_REQUEST['user_token']);
 $unserialized=unserialize($user_token);
 $sql="select * from ci_users where username=? and md5(concat(?,password))=?";
 $sth=$db->prepare($sql);
 $sth->bind_param("sss",$unserialized['username'],$cookie_seed,$unserialized['password_hash']);
 $sth->execute();
 $result = $sth->get_result();
 return $result->fetch_array(MYSQLI_ASSOC);
}

function login(){
 global $jsonobj, $db, $cookie_seed;
 $u=$jsonobj->username;
 $p=$jsonobj->password;
 $sql="select * from ci_users where username=? and password=md5(?)";
 $sth=$db->prepare($sql);
 $sth->bind_param("ss",$u,$p);
 $sth->execute();
 $result = $sth->get_result();
 if($result->num_rows==1) {
  exit(json_encode(array("user_token"=>serialize(array("username"=>$u,"password_hash"=>md5($cookie_seed . md5($p)))))));
 }
 exit("{}");

}


?>
