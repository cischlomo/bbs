<?php
ini_set('display_errors','on');
ini_set('error_reporting',E_ALL);
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
 $sql="select * from ci_forums where id=$fid";
 exit(json_encode(($m->query($sql))->fetch_all(MYSQLI_ASSOC)));
}
function viewtopic ($fid){
 global $jsonobj;

}
function getpost ($pid){
 global $jsonobj;
 print "read post# $pid";
}

function newtopic($fid){
 global $jsonobj;
 print "new topic in forum $fid\n";
 print "topic: " . $jsonobj->topic . "\n";
 print "message: " . $jsonobj->message. "\n";
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

$jsonobj=json_decode(file_get_contents("php://input"));
$router->execute($_SERVER['REQUEST_URI']);


class RegexRouter {
    
    private $getroutes = array(), $postroutes=array();
    
    public function get ($pattern, $callback) {
        $this->getroutes[$pattern] = $callback;
    }
    
    public function post ($pattern, $callback) {
        $this->postroutes[$pattern] = $callback;
    }
    
    public function execute($uri) {
        foreach (
          ($_SERVER['REQUEST_METHOD']==='POST' ? $this->postroutes : $this->getroutes)
            as $pattern => $callback) {
            if (preg_match($pattern, $uri, $params) === 1) {
                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
        }
    }
} 
//curl -H "Content-Type: application/json" -X POST -d '{"username":"xyz","password":"xyz"}' http://xbmc/p.php

?>
