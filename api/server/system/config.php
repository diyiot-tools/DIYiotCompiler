<?php

//php configuration
set_time_limit(0);
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_WARNING); //^ E_DEPRECATED ^ E_NOTICE ^ E_WARNING
ini_set('default_socket_timeout', 6000);
ini_set('display_errors','On');
ini_set('memory_limit','1024M');
ini_set('output_buffering','on');
ini_set('zlib.output_compression', 0);
date_default_timezone_set('Europe/Athens');
  
?>