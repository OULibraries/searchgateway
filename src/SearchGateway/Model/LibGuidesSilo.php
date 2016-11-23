<?php
namespace SearchGateway\Model;

/**
 * LibGuidesSile - interface with LibGuides search backend. 
 */
Class LibGuidesSilo extends Silo {

    public function __construct( $siteId, $key) {
	parent::__construct();
	$this->siteId = $siteId;
	$this->key = $key;
    }

    /*
     * Get a Result from LibGuides
     */
    public function getResult ( $query, $limit) {

	$myResult = new Result();
	$myResult->source = "libguides";
	$myResult->query = $query;
	$myResult->full = "http://link-to-full-search-tbd";

	$request = $this->client->createRequest('GET', 'http://lgapi.libapps.com/1.1/guides');
	$LibGuidesQuery = $request->getQuery();
	$LibGuidesQuery['key'] = $this->key;
	$LibGuidesQuery['site_id'] = $this->siteId;
	$LibGuidesQuery['sort_by'] = 'relevance';
	$LibGuidesQuery['search_terms'] = $query;
	$response = $this->client->send($request);

	$json = $response->json();

	/* This API doesn't return a total count or allow us to get a
	   fixed number of results, so we have to get everything and
	   manually count/limit
	*/
	$myResult->total = count($json);
        # Process hits
	$i = 0;
	foreach ($json as $key => $value) {
	    if ($i++ == $limit) break;

	    $my_title = $value['name'];
	    $my_link = $value['url'];
	    $my_description = $value['description'];

	    $myResult->addHit( $my_link, $my_title, $my_description);
	}
	return $myResult;
    }

}