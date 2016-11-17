<?php

namespace SearchGateway\Model;

Class PrimoSilo extends Silo {

    public function __construct( $primoHost, $primoKey) {
	parent::__construct();
	$this->primoHost=$primoHost;
	$this->primoKey =$primoKey;
    }

    public function getResult ( $query, $limit) {

	$myResult = new Result();
	$myResult->source = "primo";
	$myResult->query = $query;
	$myResult->full = "http://link-to-full-search-tbd";


	# Do primo search
	# See API docs
	# https://developers.exlibrisgroup.com/primo/apis/webservices/rest/pnxs
	$primoRequest = $this->client->createRequest('GET', $this->primoHost."/primo/v1/pnxs");
	$primoQuery = $primoRequest->getQuery();
	$primoQuery['q'] = 'any,contains,' . $query;
	$primoQuery['limit'] = $limit;
	$primoQuery['apikey'] = $this->primoKey;
	$primoQuery['vid'] = 'OU';
	$primoQuery['scope'] = 'default_scope';
	$primoQuery['addfields'] =['pnxId'];

	$primoResponse = $this->client->send($primoRequest);
	$primoJson = $primoResponse->json();

	# How many hits did we get?
	$myResult->total = $primoJson['info']['total'];

	# Process hits
	foreach ($primoJson['docs'] as $docs) {

	    $my_title = $docs['title'];
	    $my_link  = "http://ou-primo.hosted.exlibrisgroup.com/OU:default_scope:".$docs['pnxId'];
	    $my_description  = ""; // no good source for description known 

	    $myResult->addHit( $my_title, $my_link, $my_description);
	}

	return $myResult;
    }

}