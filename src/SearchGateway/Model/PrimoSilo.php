<?php

namespace SearchGateway\Model;

Class PrimoSilo extends Silo {

    public function __construct() {
	;
    }

    public function getResult ( $query, $limit) {
	// TODO these will get factored into the constructor once we've fleshed out the base class more.
	// TODO maybe with other OU specific stuff?
	// TODO still need to figure out books-only and journals-only searches
	global $global_primo_uri, $global_primo_key; 


	$myResult = new Result();
	$myResult->source = "primo";
	$myResult->query = $query;
	$myResult->full = "http://link-to-full-search-tbd";

	# Setup web client
	$client = new \GuzzleHttp\Client();
	$jar = new \GuzzleHttp\Cookie\CookieJar();

	# Do primo search
	# See API docs
	# https://developers.exlibrisgroup.com/primo/apis/webservices/rest/pnxs
	$primoRequest = $client->createRequest('GET', $global_primo_uri);
	$primoQuery = $primoRequest->getQuery();
	$primoQuery['q'] = 'any,contains,' . $query;
	$primoQuery['limit'] = $limit;
	$primoQuery['apikey'] = $global_primo_key;
	$primoQuery['vid'] = 'OU';
	$primoQuery['scope'] = 'default_scope';
	$primoQuery['addfields'] =['pnxId'];

	$primoResponse = $client->send($primoRequest);
	$primoJson = $primoResponse->json();

	# How many hits did we get?
	$myResult->total = $primoJson['info']['total'];

	# Process hits
	foreach ($primoJson['docs'] as $docs) {
	    $row = new \stdClass();
	    $row->title = $docs['title'];
	    $row->link  = "http://ou-primo.hosted.exlibrisgroup.com/OU:default_scope:".$docs['pnxId'];
	    $myResult->hits[] = $row;
	}

	return $myResult;
    }

}