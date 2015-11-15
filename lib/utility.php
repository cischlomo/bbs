<?php
class Utility {
 static function getuser(){
  global $httproot, $cookie_name;
  $url=$httproot . "/api/me";
  $unserialized=unserialize($_COOKIE[$cookie_name]);
  if(isset($unserialized['user_token'])) {
   $qs="user_token=" . urlencode($unserialized['user_token']);
   return Utility::curlstuff($url . "?" . $qs); 
  }
  return NULL;
 }
 
 /*** utility function to call the api with *****/
 const GET=0;
 const POST=1;
 const DELETE=2;
 static function curlstuff($url, $method=self::GET){
  $curlopts= array( 
 	 CURLOPT_SSL_VERIFYPEER => 0,
         CURLOPT_HEADER => 0, 
 	 CURLOPT_RETURNTRANSFER => 1,
         CURLOPT_URL => $url
  );
  if ($method==self::POST){
   $curlopts[CURLOPT_POST] = 1;
   if (isset($_REQUEST)){
    $curlopts[CURLOPT_POSTFIELDS] = json_encode($_REQUEST);
   }
  }
  else if ($method==self::DELETE){
   $curlopts[CURLOPT_CUSTOMREQUEST]="DELETE";
   if (isset($_REQUEST)){
    $curlopts[CURLOPT_POSTFIELDS] =json_encode($_REQUEST);
   }
  }
  $ch = curl_init();
  curl_setopt_array($ch,$curlopts);
  $output= json_decode(curl_exec($ch));
  //error_log(curl_error($ch));
  //error_log("output: " . print_r($output,1));
  curl_close($ch);
  return $output;
 }
 
}

?>
