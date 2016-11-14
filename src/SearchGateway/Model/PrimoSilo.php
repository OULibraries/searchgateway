<?php

namespace SearchGateway\Model;

Class PrimoSilo extends Silo {

    public function __construct() {
	;
    }

    public function getResult ( $query, $limit) {
	global $global_primo_uri, $global_primo_key;

       	
	$myResult = new Result();
	$myResult->source = "primo";
	$myResult->query = $query;

	# Setup web client
	$client = new \GuzzleHttp\Client();
	$jar = new \GuzzleHttp\Cookie\CookieJar();

	# Do primo search
	$primoRequest = $client->createRequest('GET', $global_primo_uri);
	$primoQuery = $primoRequest->getQuery();
	$primoQuery['q'] = 'any,contains,' . $query;
	$primoQuery['limit'] = $limit;
	$primoQuery['apikey'] = $global_primo_key;
	$primoQuery['loc'] = "adaptor,primo_central_multiple_fe";
	$primoResponse = $client->send($primoRequest);
	$primoJson = $primoResponse->json();

	# How many hits did we get?
	$myResult->total = $primoJson['info']['total'];

	# Process hits
	foreach ($primoJson['docs'] as $docs) {
	    $row = new \stdClass();
	    $row->title = $docs['title'];
	    $row->link  = $docs['delivery']['GetIt1'][0]['links'][0]['link'];
	    $myResult->hits[] = $row;
	}

	return $myResult;
    }

}