<?php
function compilesketch($payload,$storage){
    global $app;
    $result["controller"] = __FUNCTION__;
    $result["function"] = substr($app->request()->getPathInfo(),1);
    $result["method"] = $app->request()->getMethod();
    $params = loadParameters();
    $result->function = substr($app->request()->getPathInfo(),1);
    $result->method = $app->request()->getMethod();
    $params = loadParameters();
    $srcfile = urldecode(base64_decode(urldecode($params["srcfile"])));
    $compiler = $params["compiler"];
    $filename = $params["filename"];

    if($compiler == 'gcc') {
        $tmpfile = tempnam('/tmp', 'avrsrc').'.c';
        file_put_contents($tmpfile, $srcfile);

       try {
            putenv("PATH=" .getenv('PATH'). ':/var/www/html/tools/build-tools/avr-gcc/src/x64/avr/bin');
            $output = shell_exec("/var/www/html/tools/build-tools/avr-gcc/compile.sh ".$tmpfile.' 2>&1; echo $?');
            if($output != null) {
                $result["output"]=  $output;
                $result["hex"]= base64_encode(file_get_contents($tmpfile.'.hex'));
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
                $result["status"] = "200";
                $result["result"]=  "ok";
            } else {
                $result["output"]=  $output;
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: Error";
                $result["status"] = "500";
                $result["result"]=  "ok";
            }
        } catch (Exception $e) {
            $result["output"] = $e->getCode();
            $result["status"] = $e->getCode();
            $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
        }
        @unlink($tmpfile);
        @unlink($tmpfile.'.hex');
    } else if($compiler == 'ino') {
        $tmpfile = tempnam('/tmp', 'avrsrc').'.ino';
        file_put_contents($tmpfile, $srcfile);
        
       try {
            echo 'not supported yet'; die();
            putenv("PATH=" .getenv('PATH'). ':/var/www/html/tools/build-tools/ino/ino/bin');
            $output = shell_exec("/var/www/html/tools/build-tools/ino/ino/bin/ino build ".$tmpfile.' 2>&1; echo $?');
            if($output != null) {
                $result["output"]=  $output;
                $result["hex"]= base64_encode(file_get_contents($tmpfile.'.hex'));
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
                $result["status"] = "200";
                $result["result"]=  "ok";
            } else {
                $result["output"]=  $output;
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: Error";
                $result["status"] = "500";
                $result["result"]=  "ok";
            }
        } catch (Exception $e) {
            $result["output"] = $e->getCode();
            $result["status"] = $e->getCode();
            $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
        }
        @unlink($tmpfile);
        @unlink($tmpfile.'.hex');
    } else {
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: UnsupportedCompiler";
        $result["status"] = "500";
        $result["result"]=  "ok";
    }

    return $result;
}
?>
