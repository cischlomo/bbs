<?php
class RegexRouter {
    
    private $getroutes = array(), $postroutes=array();
    
    public function post ($pattern, $callback) {
        $this->postroutes[$pattern] = $callback;
    }
    
    public function get ($pattern, $callback) {
        $this->getroutes[$pattern] = $callback;
    }

    function __construct($arg){
     $prefix=$arg['prefix'];
     foreach ($arg['get'] as $k=>$v){
       $this->get($prefix . "\/" . $k . "\/([0-9]+)\/?$/",$v);
     }
     foreach ($arg['post'] as $k=>$v){
       $this->post($prefix . "\/" . $k . "\/([0-9]+)\/?$/",$v);
     }
     //header("Content-type: text/plain");exit(print_r(get_object_vars($this),1));

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
          if(empty($getroutes) && empty($postroutes)){
           exit("invalid url");
          }
    }
} 
?>
