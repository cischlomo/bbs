<?php
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);

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
 exit(json_encode(array("tid"=>$tid,"subject"=>$subject,"posts"=>$resp)));
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
  exit(json_encode(array("error"=>"post is not contained by a topic")));
 }
}
require_once('lib/RegexRouter.php');
$db=new Mysqli("localhost","root");
$db->select_db("campidiot");
$router = new RegexRouter(array(
 "prefix"=>"/^\/bbs\/api",
 "get"=>array(
  "forum"=>"viewforum",
  "topic"=>"viewtopic",
  "post"=>"getpost",
  "nt"=>"newtopicform",
  ),
 "post"=>array(
  "forum"=>"newtopic",
  "topic"=>"replytotopic",
  "post"=>"replytopost")
 ));
 

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
 $jsonobj->cookie=$_COOKIE;
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
 $sql="select * from ci_topics where forum_id=$fid";
 exit(json_encode(($db->query($sql))->fetch_all(MYSQLI_ASSOC)));
}
function replytopost ($pid){
 global $jsonobj;
 print "reply to post $pid<p>";
 print "message: " . $jsonobj->message;
}

//exit(file_get_contents("php://input"));
$jsonobj=json_decode(file_get_contents("php://input"));
//exit(print_r($jsonobj,1));
$router->execute($_SERVER['REQUEST_URI']);

//curl -H "Content-Type: application/json" -X POST -d '{"username":"xyz","password":"xyz"}' http://xbmc/p.php

?>
