<?php

namespace SearchGateway\Model;

Class LibAnswersSilo extends Silo {

    public function __construct() {
	;
    }

    public function getResult ( $query, $limit) {

	global $global_libanswers_key, $global_libanswers_siteid;


	$myResult = new Result();
	$myResult->source = "libanswers";
	$myResult->query = $query;
	$myResult->full = "http://link-to-full-search-tbd";

	# Setup web client
	$client = new \GuzzleHttp\Client();
	$jar = new \GuzzleHttp\Cookie\CookieJar();

	

	
	// libapps api
	$request = $client->createRequest('GET', 'http://lgapi.libapps.com/1.1/guides');
	$LibAnswersQuery = $request->getQuery();
	$LibAnswersQuery['key'] = $global_libanswers_key;
	$LibAnswersQuery['site_id'] = $global_libanswers_siteid;
	$LibAnswersQuery['sort_by'] = 'relevance';
	$LibAnswersQuery['search_terms'] = $query;
	$response = $client->send($request);

	$json = $response->json();



	$myResult->total = count($json);

        # Process hits
	$i = 0;
	foreach ($json as $key => $value) {
	    $row = new \stdClass();
	    $row->title = $value['name'];
	    $row->link = $value['url'];
	    $row->description = $value['description'];
	    $myResult->hits[] = $row;
	    if (++$i == $limit) break;
	}
	return $myResult;
    }

}