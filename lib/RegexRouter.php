<?php
class RegexRouter {
    
    private $getroutes = array(), $postroutes=array(), $deleteroutes=array();
    
    public function delete ($pattern, $callback) {
        $this->deleteroutes[$pattern] = $callback;
    }
    
    public function post ($pattern, $callback) {
        $this->postroutes[$pattern] = $callback;
    }
    
    public function get ($pattern, $callback) {
        $this->getroutes[$pattern] = $callback;
    }

    function __construct($arg){
     $prefix=$arg['prefix'];
     foreach ($arg['get'] as $k=>$v){
       $this->get($prefix . "\/" . $k . "\/([0-9]+)\/?$/",$v) || //with arg
       $this->get($prefix . "\/" . $k . "\/?$/",$v) || //or without
       $this->get($prefix . "\/" . $k . "\/?([^?]*)\?[^\/]*$/",$v); //QueryString
     }
     foreach ($arg['post'] as $k=>$v){
       $this->post($prefix . "\/" . $k . "\/([0-9]+)\/?$/",$v) || //with arg
       $this->post($prefix . "\/" . $k . "\/?$/",$v); //or without
     }

     foreach ($arg['delete'] as $k=>$v){
       $this->delete($prefix . "\/" . $k . "\/([0-9]+)\/?$/",$v);
     }
     //header("Content-type: text/plain");exit(print_r(get_object_vars($this),1));

    }
    
    public function execute($uri) {
        $routes=array();
          switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
           case 'POST':
            $routes=$this->postroutes;
            break;
           case 'DELETE':
            $routes=$this->deleteroutes;
            break;
           case 'GET':
           default:
            $routes=$this->getroutes;
          }
        foreach ($routes as $pattern => $callback) {
            if (preg_match($pattern, $uri, $params) === 1) {
                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
          }
         exit("invalid url");
    }
}
?>
