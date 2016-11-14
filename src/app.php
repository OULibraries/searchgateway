<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../config/secrets.php';



use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = new Application;

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

    $mySearchApi = new SearchGateway\Model\PrimoSilo();


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