<?php
// ロガー
class Logger 
{
  public function log($throwable) {
    $error_msg = "";
    
    if(!empty($throwable)){
    	$error_msg = $throwable->__toString();
    }
    
    error_log($error_msg);
  }
}
