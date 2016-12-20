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

  $api    = isset($params["t"]) ? $params["t"] : "";     // search api to target
  $limit  = isset($params["n"]) && ctype_digit($params["n"]) ? $params["n"] : "5";  // number of results requested
  $needle = isset($params["q"]) ? $params["q"] : "";  // query needle

  $needle = preg_replace('/[^\p{L}\p{N}]+/u', '', $needle); // strip out anything that isn't a (unicode) alphanumeric

  $option = 'default';

  switch ($api) {
    case "primo":
      $mySearchApi = new SearchGateway\Model\PrimoSilo($conf['primo_host'], $conf['primo_key'], $conf['vid'], $option);
      break;
    case "libguides":
      $mySearchApi = new SearchGateway\Model\LibGuidesSilo($conf['libguides_siteid'], $conf['libguides_key']);
      break;
    case 'primobooks':
      $option = 'books';
      $mySearchApi = new SearchGateway\Model\PrimoSilo($conf['primo_host'], $conf['primo_key'], $conf['vid'], $option);
      break;
    case 'primoshareok':
      $option = 'share';
      $mySearchApi = new SearchGateway\Model\PrimoSilo($conf['primo_host'], $conf['primo_key'], $conf['vid'], $option);
      break;
    case 'collection':
      $option = 'collection';
      $mySearchApi = new SearchGateway\Model\PrimoSilo($conf['primo_host'],$conf['primo_key'], $conf['vid'], $option);
      break;
    default:
        throw new Exception('No valid search!');
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
 * Pretend like we have error handling
 */
$app->error(function (Exception $e, Request $request, $code) {
   error_log($e);
   $envelope = [];
   $envelope['status'] = $code;
   $envelope['error'] = $e->getMessage();
   return new JsonResponse($envelope);
});


/*
 * Our main use case is client side, so allow for jsonp
 */
$app->after(function (Request $request, Response $response) {
    if(($response instanceof JsonResponse) && $request->get('callback')) {
        $response->setCallback($request->get('callback'));
    }
});



$app->run();