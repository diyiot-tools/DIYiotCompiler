<?php
function writesketch($payload,$storage){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    $params = loadParameters();
    $binfile = $params["binfile"];

    //$binfile = $app->request->post('binfile');
    $up=json_decode(base64_decode($payload));
    $client_id=$up->client_id;
    $binfilename = base64_decode($binfile);
   try {
	mkdir("tmp");
	$file="tmp/file.hex";
	file_put_contents($file, $binfilename);

	$output1 = shell_exec("/etc/init.d/diyiotsocat stop");
    	$result["diyiotsocatistop"]=  $output1;
    	$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    	$result["status"] = "200";
    	$result["result"]=  "ok";
     } catch (Exception $e) {
        $result["hex"] = $e->getCode();
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
     }

   try {
	//$output = shell_exec("stty -F /dev/ttyACM0   115200; /usr/bin/avrdude -F -V -c arduino -p ATMEGA328P -P /dev/ttyACM0 -b 115200 -U flash:w:$file; /etc/init.d/diyiotsocat start");
	$output = shell_exec("stty -F /dev/ttyACM0   115200; /usr/bin/avrdude -F -V -c arduino -p ATMEGA328P -P /dev/ttyACM0 -b 115200 -U flash:w:$file");
    	$result["avrdude"]=  $output;
    	$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    	$result["status"] = "200";
    	$result["result"]=  "ok";
     } catch (Exception $e) {
        $result["avrdude"] = $e->getCode();
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
     }

   try {
	$output2 = shell_exec("/etc/init.d/diyiotsocat start");
    	$result["diyiotsocatstart"]=  $output2;
    	$result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
    	$result["status"] = "200";
    	$result["result"]=  "ok";
     } catch (Exception $e) {
        $result["diyiotsocatstart"] = $e->getCode();
        $result["status"] = $e->getCode();
        $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
     }

    return $result;
}
?>
