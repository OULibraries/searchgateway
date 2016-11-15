<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../config/secrets.php';
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = new Application;
$app['debug'] = true;

function searchController( Request $request){
    
    /* Process incoming query 
     */
    $params = $request->query->all();
    $api = $params["t"];     // search api to target
    $needle = $params["q"];  // query needle
    $limit  = $params["n"];  // number of results requested
    
    /* 
     *  Determine appropriate Silo for search. Ideally, we'll
     *  eventually do something clever so that we don't have to
     *  bootstrap a Silo option whenever we run a query.
     */

    global $global_primo_host, $global_primo_key, $global_libguides_siteid, $global_libguides_key;


    switch ($api) {
    case "primo":
	$mySearchApi = new SearchGateway\Model\PrimoSilo($global_primo_host, $global_primo_key);
        break;
    case "libguides":
	$mySearchApi = new SearchGateway\Model\LibGuidesSilo($global_libguides_siteid, $global_libguides_key);
        break;
    }
    


    /*
     *  Get Result from Silo. 
     */
    $mySearch  = $mySearchApi->getResult($needle, $limit);
	

    /*  Return error or Result as enveloped JSON 
     */
    $envelope = [];
    $envelope['data'] = $mySearch->getData();

    return new JsonResponse( $envelope );
}

/*  Basic query API
 *  http://localhost:8888/search?t=shareok&q=foobarbaz&n=10
 */
$app->get('/search',"searchController");

$app->run();