<?php
function viewtopic ($tid){
 global $jsonobj,$m;
 $sql="select t.subject as topic, p.id as pid, p.message as message from ci_posts as p left join ci_topics as t on t.id=p.topic_id where t.id=$tid group by t.id, p.id";
 exit(json_encode($m->query($sql)->fetch_all(MYSQLI_ASSOC)));
}
function newtopic($fid){
 global $jsonobj,$m;
 //print "new topic in forum $fid\n";
 //print "topic: " . $jsonobj->topic . "\n";
 //print "message: " . $jsonobj->message. "\n";
 $jsonobj->cookie=$_COOKIE;
 $sql="insert into ci_topics (subject,posted) values (?,?)";
 $sth=$m->prepare($sql) or die($m->error);
 $d=time();
 $sth->bind_param("si",$jsonobj->subject,$d);
 $sth->execute();
 exit(json_encode(array("topic_id"=>$m->insert_id)));
}
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);
require_once('../lib/RegexRouter.php');
$m=new Mysqli("localhost","root");
$m->select_db("campidiot");
$router = new RegexRouter();
$prefix="/^\/bbs\/api";

$router->get( $prefix . '\/forum\/([0-9]+)\/?$/',  "viewforum");
$router->get( $prefix . '\/topic\/([0-9]+)\/?$/',  "viewtopic");
$router->get( $prefix . '\/post\/([0-9]+)\/?$/',   "getpost");

$router->post( $prefix . '\/forum\/([0-9]+)\/?$/',  "newtopic");
$router->post( $prefix . '\/topic\/([0-9]+)\/?$/', "replytotopic");
$router->post( $prefix . '\/post\/([0-9]+)\/?$/',  "replytopost");
 
function viewforum ($fid){
 global $jsonobj, $m;
 $sql="select * from ci_topics where forum_id=$fid";
 exit(json_encode(($m->query($sql))->fetch_all(MYSQLI_ASSOC)));
}
function getpost ($pid){
 global $jsonobj;
 print "read post# $pid";
}

function replytotopic ($tid){
 global $jsonobj;
 print "reply to topic $tid<p>";
 print "message: " . $jsonobj->message;
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
