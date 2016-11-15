<?php

namespace SearchGateway\Model;

Class LibGuidesSilo extends Silo {

    public function __construct( $siteId, $key) {
	parent::__construct();
	$this->siteId = $siteId;
	$this->key = $key;
    }

    public function getResult ( $query, $limit) {

	$myResult = new Result();
	$myResult->source = "libguides";
	$myResult->query = $query;
	$myResult->full = "http://link-to-full-search-tbd";

	
	// libapps api
	$request = $this->client->createRequest('GET', 'http://lgapi.libapps.com/1.1/guides');
	$LibGuidesQuery = $request->getQuery();
	$LibGuidesQuery['key'] = $this->key;
	$LibGuidesQuery['site_id'] = $this->siteId;
	$LibGuidesQuery['sort_by'] = 'relevance';
	$LibGuidesQuery['search_terms'] = $query;
	$response = $this->client->send($request);

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