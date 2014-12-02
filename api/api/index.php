<?php
//header("Content-Type: text/html; charset=utf-8");
header('Content-Type: application/json');

chdir("../server");
require_once('system/includes.php');
require_once('libs/Slim/Slim.php');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->config('debug', true);

//here set all routes 
$app->map('/compilesketch', 'compilesketchController')->via('POST');

//function not found
$app->notFound(function () use ($app) 
{
    $controller = $app->environment();
    $controller = substr($controller["PATH_INFO"], 1);

    try
    {
       if ( strtoupper($app->request()->getMethod() != 'GET'))
            throw new Exception(ExceptionMessages::MethodNotFound, ExceptionCodes::MethodNotFound);
        else
            throw new Exception(ExceptionMessages::FunctionNotFound, ExceptionCodes::FunctionNotFound);
    } 
    catch (Exception $e) 
    {
        $result["status"] = $e->getCode();
        $result["message"] = "[".$app->request()->getMethod()."][".$controller."]:".$e->getMessage();
    }

    echo  json_encode( $result ); 
    //echo toGreek( json_encode( $result ) ); 

});

$app->run();

#=========================================================================

function PrepareResponse()
{
    global $app;

    $app->contentType('application/json');
    $app->response()->headers()->set('Content-Type', 'application/json; charset=utf-8');
    $app->response()->headers()->set('X-Powered-By', 'diyiot-tools');
    $app->response()->setStatus(200);
}


function UrlParamstoArray($params)
{
    $items = array();
    foreach (explode('&', $params) as $chunk) {
        $param = explode("=", $chunk);
        $items = array_merge($items, array($param[0] => urldecode($param[1])));
    }
    return $items;

}

function loadParameters()
{
    global $app;

    if ($app->request->getBody())
    {
        if ( is_array( $app->request->getBody() ) )
            $params = $app->request->getBody();
        else if ( json_decode( $app->request->getBody() ) )
            $params = get_object_vars( json_decode($app->request->getBody(), false) );
        else
            $params = UrlParamstoArray($app->request->getBody());
    }
    else
    {
        if ( json_decode( key($_REQUEST) ) )
            $params = get_object_vars( json_decode(key($_REQUEST), false) );
        else
            $params = $_REQUEST;
    }
    
    // array to object
    //$params = json_decode (json_encode ($params), FALSE);

    return $params;
}

/*
function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

function toGreek($value)
{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $value ? $value : array());
}
*/

#=======Controllers ========================================================================
#===========================================================================================
 

function compilesketchController()
{
    global $app;
    $params = loadParameters();
		 
    switch ( strtoupper( $app->request()->getMethod() ) )
    {
        case 'POST': 
            $result = compilesketch(
                $params["srcfile"]       
            );      
            break;
    }

    PrepareResponse();
    $app->response()->setBody( json_encode( $result )  );
}

?>
