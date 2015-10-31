<?php
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);
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
 
function getpost ($pid){
 global $jsonobj,$db;
 $sql="select topic_id from ci_posts where id=$pid";
 list($tid)=($db->query($sql))->fetch_row();
 if ($tid>0){
  //viewtopic($tid);
  exit(json_encode(array("tid"=>$tid,"pid"=>$pid)));
 } else {
  exit(json_encode(array("error"=>"post is not contained by a topic")));
 }
}
function viewtopic ($tid){
 global $jsonobj,$db;
 $sql="select subject from ci_topics where id=$tid";
 list($subject)=$db->query($sql)->fetch_row() or die ($db->error);
 $sql="select id,message,replyto,id as pid from ci_posts where topic_id=$tid";
 $resp=$db->query($sql)->fetch_all(MYSQLI_ASSOC);
 $output=array("tid"=>$tid,"subject"=>$subject,"posts"=>$resp);
 exit(json_encode($output));
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
