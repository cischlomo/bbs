<?php
 namespace Utility;
  
  $user_token=NULL;
  if(isset($_COOKIE[$cookie_name])) {
	 $user_token=$_COOKIE[$cookie_name];
  }
  //exit("<pre>$user_token");
  

 /*** utility function to call the api with *****/
 const GET=0;
 const POST=1;
 const DELETE=2;
 function api($url, $method=GET){
	 global $user_token,$httproot;
	 $url=$httproot . $url;
  if (isset($user_token)){
	  $url .= "?user_token=" . urlencode($user_token);
	  //exit("$url");
  }
  //exit($url);
  $curlopts= array( 
 	 CURLOPT_SSL_VERIFYPEER => 0,
         CURLOPT_HEADER => 0, 
 	 CURLOPT_RETURNTRANSFER => 1,
         CURLOPT_URL => $url
  );
  if ($method==POST){
   $curlopts[CURLOPT_POST] = 1;
   if (isset($_REQUEST)){
    $curlopts[CURLOPT_POSTFIELDS] = json_encode($_REQUEST,1);
   }
  }
  else if ($method==DELETE){
   $curlopts[CURLOPT_CUSTOMREQUEST]="DELETE";
   if (isset($_REQUEST)){
    $curlopts[CURLOPT_POSTFIELDS] =json_encode($_REQUEST,1);
   }
  }
  $ch = curl_init();
  curl_setopt_array($ch,$curlopts);
  $output= json_decode(curl_exec($ch),TRUE);
  //error_log(curl_error($ch));
  //exit("output: " . print_r($output,1));
  curl_close($ch);
  return $output;
 }
 


?>
