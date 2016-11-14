<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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


    /*
     *  Get Result from Silo. 
     */
	



    /*  Return Result as Jason 
     */
    $out = [];
    return new Response( json_encode($out) );
}

    /*  Basic query API
	http://localhost:8888/search?t=shareok&q=foobarbaz&n=10
     */
$app->get('/search',"searchController");

$app->run();