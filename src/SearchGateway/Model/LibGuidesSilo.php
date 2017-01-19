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
	$myResult->full = "http://guides.ou.edu/srch.php?q=" . $query . "&t=0";

	$request = $this->client->createRequest('GET', 'http://lgapi.libapps.com/1.1/guides');
	$LibGuidesQuery = $request->getQuery();

	$LibGuidesQuery['key'] = $this->key; //OU specific private ky
	$LibGuidesQuery['site_id'] = $this->siteId; //the OU site ID...could change...that's why it's set as a variable
	$LibGuidesQuery['sort_by'] = 'relevance'; //default is by date...we want to sort by relevance
	$LibGuidesQuery['search_terms'] = $query; //the search terms
	$response = $this->client->send($request);

	$json = $response->json();

	/* This API doesn't return a total count or allow us to get a
	   fixed number of results, so we have to get everything and
	   manually count/limit
	*/
	$myResult->total = count($json);
    $myResult->plural = $this->isPlural($myResult->total);
    $myResult->topLabel = 'Research Guide';

    # Process hits
	$i = 0;
    $noShowArray = array('Internal Guide', 'Course Guide', 'Template Guide');
    $sentData = array();

	foreach ($json as $key => $value) {
	    if ($i++ == $limit) break;
        if ($value['status_label'] != 'Published') continue;
        if (in_array($value['type_label'], $noShowArray)) continue;

	    $sentData['my_title'] = $value['name'];
	    $sentData['my_link'] = $value['url'];
	    $sentData['subjects'] = $value['description'];
        $sentData['date'] = $value['published'];
        $sentData['creator'] = FALSE; // $value['owner_id']; uncomment if we want the creator
        $sentData['type'] = 'guide';


	    $myResult->addHit($sentData);
	}

	return $myResult;
    }

}