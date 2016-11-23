<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/secrets.php';


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = new Application;
$app['debug'] = TRUE;

/**
 * Route searches to the appropriate search silo
 */
function searchController(Request $request) {

  global $conf; // settings from secrets.php

  /*
   * Process incoming query
   */
  $params = $request->query->all();
  $api = $params["t"];     // search api to target
  $needle = $params["q"];  // query needle
  $limit = $params["n"];  // number of results requested
  $book = FALSE;

  switch ($api) {
    case "primo":
      $mySearchApi = new SearchGateway\Model\PrimoSilo($conf['primo_host'], $conf['primo_key'], $book);
      break;
    case "libguides":
      $mySearchApi = new SearchGateway\Model\LibGuidesSilo($conf['libguides_siteid'], $conf['libguides_key']);
      break;
    case 'primoBook':
      $book = TRUE;
      $mySearchApi = new SearchGateway\Model\PrimoSilo($conf['primo_host'], $conf['primo_key'], $book);
      break;
  }

  /*
   *  Get Result from Silo.
   */
  $mySearch = $mySearchApi->getResult($needle, $limit);

  /*  Return error or Result as enveloped JSON
   */
  $envelope = [];
  $envelope['data'] = $mySearch->getData();

  return new JsonResponse($envelope);
}

/*
 * Basic query API
 * Support queries like http://localhost:8888/search?t=primo&q=christmas&n=5
 * or http://localhost:8888/search?t=primo&q=christmas&n=5&callback=jsonp
 */
$app->get('/search', "searchController");


/*
 * Our main use case is client side, so allow for jsonp
 */
$app->after(function (Request $request, Response $response) {
    if(($response instanceof JsonResponse) && $request->get('callback')) {
        $response->setCallback($request->get('callback'));
    }
});



$app->run();