<?php
class SitemapLog 
{
  const TYPE_MSG = 0;
  const TYPE_WARN = 1;
  const TYPE_ERR  = 2;

  function message($message)
  {
    $this->log($message);
  }

  function warning($message)
  {
    $this->log($message,self::TYPE_WARN);
  }

  function error($message)
  {
    $this->log($message,self::TYPE_ERR);
  }

  function log($message, $type = self::TYPE_MSG)
  {
     switch($type)
     {
       case self::TYPE_MSG:
         echo $message.PHP_EOL;
       break;

       case self::TYPE_WARN:
         echo "WARNING: $message".PHP_EOL;
       break;

       case self::TYPE_ERR:
         echo "ERROR: $message".PHP_EOL;
         throw new Exception($message);
       break;
     }
  }
}
