<?php
function zipSketch($folder) {
    $tmpzip = tempnam('/tmp', 'avrsrczip'.md5($folder)).'.tgz';
    $output = shell_exec("tar -czvf ".$tmpzip." ".$folder.' 2>&1; echo $?');
    $outputParts = explode("\n", $output);
    if($outputParts[count($outputParts)-2] != '0') {
        $result["output"]=  $output;
        throw new \Exception('Could not zip project dir: '.trim($output));
    }
    if(!file_exists($tmpzip)) {
        throw new \Exception('Zip file could not be created: '.trim($tmpzip));
    }
    $content = file_get_contents($tmpzip);
    @unlink($tmpzip);
    return base64_encode($content);
}

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
    $srclib = array();
    foreach($_POST['srclib'] as $curName => $curFile) {
        $srclib[$curName] = urldecode(base64_decode(urldecode($curFile)));
    }
    $compiler = $params["compiler"];
    $filename = $params["filename"];

    if($compiler == 'gcc') {
        $tmpfile = tempnam('/tmp', 'avrsrc').'.c';
        file_put_contents($tmpfile, $srcfile);

       try {
            putenv("PATH=" .getenv('PATH'). ':/var/www/html/tools/build-tools/avr-gcc/src/x64/avr/bin');
            $output = shell_exec("/var/www/html/tools/build-tools/avr-gcc/compile.sh ".$tmpfile.' 2>&1; echo $?');
            if($output != null && file_exists($tmpfile.'.hex')) {
                //$result["output"]=  $output; // Don't show output when there were no errors
                $result["zip" ]= zipSketch($tmpfile);
                $result["hex"] = base64_encode(file_get_contents($tmpfile.'.hex'));
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
                $result["status"] = "200";
                $result["result"] =  "ok";
            } else {
                $result["output"] =  $output;
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: Error";
                $result["status"] = "500";
                $result["result"] =  "error";
            }
        } catch (Exception $e) {
            $result["output"] = $e->getCode();
            $result["status"] = $e->getCode();
            $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
        }
        @unlink($tmpfile);
        @unlink($tmpfile.'.hex');
    } else if($compiler == 'ino') {
        $tmpfile = tempnam('/tmp', 'avrsrc').'1';
        
       try {
            if(!mkdir($tmpfile)) {
                throw new \Exception('Could not create project directory: '.$tmpfile);
            }
            putenv("PATH=" .getenv('PATH'). ':/var/www/html/tools/build-tools/ino/ino/bin');
            // Create project folder and init
            $output = shell_exec("cd ".$tmpfile."; /var/www/html/tools/build-tools/ino/ino/bin/ino init 2>&1; echo $?");
            if(trim($output) != '0') { throw new \Exception('Could not init project dir: '.trim($output)); }
            file_put_contents($tmpfile.'/src/sketch.ino', $srcfile);
            foreach($srclib as $curName => $curFile) {
                if(!is_dir(dirname($tmpfile.'/lib/'.$curName))) { mkdir(dirname($tmpfile.'/lib/'.$curName), 0777, true); }
                file_put_contents($tmpfile.'/lib/'.$curName, $curFile);
            }
            $zipSketch = zipSketch($tmpfile);
            $output = shell_exec("cd ".$tmpfile."; /var/www/html/tools/build-tools/ino/ino/bin/ino build 2>&1; echo $?");
            $outputParts = explode("\n", $output);
            if($outputParts[count($outputParts)-2] != '0') {
                $result["output"]=  $output;
                throw new \Exception('Compilation failed');
            }
            if($outputParts[count($outputParts)-2] == '0' && file_exists($tmpfile.'/.build/uno/firmware.hex')) {
                //$result["output"]=  $output; // Don't show output when there were no errors
                $result["zip"] = $zipSketch;
                $result["hex"] = base64_encode(file_get_contents($tmpfile.'/.build/uno/firmware.hex'));
                $result["message"] = "[".$result["method"]."][".$result["function"]."]: NoErrors";
                $result["status"] = "200";
                $result["result"] =  "ok";
            } else {
                $result["output"] =  $output;
                throw new \Exception('Compilation failed');
            }
        } catch (Exception $e) {
            $result["status"] = "500";
            $result["message"] = "[".$result["method"]."][".$result["function"]."]:".$e->getMessage();
            $result["result"] =  "error";
        }
        @unlink($tmpfile);
        @unlink($tmpfile.'.hex');
    } else {
        $result["message"] = "[".$result["method"]."][".$result["function"]."]: UnsupportedCompiler";
        $result["status"] = "500";
        $result["result"] =  "error";
    }

    return $result;
}
?>
