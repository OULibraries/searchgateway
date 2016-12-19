<?php
namespace SearchGateway\Model;

/*
 * PrimoSilo - interface with Primo search backend. 
 *
 */
Class PrimoSilo extends Silo {

  public function __construct($primoHost, $primoKey, $option, $vid) {
    parent::__construct();
    $this->primoHost = $primoHost;
    $this->primoKey = $primoKey;
    $this->primoBook = $option;
    $this->vid = $vid;
  }

  /*
   * Get a Result from Primo
   */
  public function getResult($query, $limit) {

    #only set this is it is for 'books only'
    $bookSearchArg = ($this->primoBook != 'default') ? '&mode=advanced' : '';

    $myResult = new Result();
    $myResult->source = "primo";
    $myResult->query = $query;
    $myResult->full = "//ou-primo.hosted.exlibrisgroup.com/primo-explore/search?query=any,contains," . $query . $bookSearchArg . "&search_scope=default_scope&vid=" . $this->vid . "&sortby=rank";


    # Do primo search
    # See API docs
    # https://developers.exlibrisgroup.com/primo/apis/webservices/rest/pnxs
    $primoRequest = $this->client->createRequest('GET', $this->primoHost . "/primo/v1/pnxs");
    $primoQuery = $primoRequest->getQuery();
    $primoQuery['q'] = 'any,contains,' . $query; //the search term
    $primoQuery['limit'] = $limit; //number of records to return
    $primoQuery['apikey'] = $this->primoKey; //private key for OU
    $primoQuery['vid'] = $this->vid; //identification code...right now it is 'OUNEW'
    $primoQuery['scope'] = 'default_scope'; //range of types to return...default is everything
    $primoQuery['addfields'] = ['pnxId']; //specific identifier for individual records
    $primoQuery['view'] = 'full'; //view = full will return everything...including the subject or description

    #if this is 'books only' then we need to set that facet type
    if ($this->primoBook == 'books') {
      $primoQuery['qInclude'] = 'facet_rtype,exact,books';
      $myResult->source = "primobooks";
      $myResult->topLabel = 'Book';
    }
    elseif ($this->primoBook == 'collection') {
      $primoQuery['qInclude'] = 'facet_local5,exact,Bass Collection';
      $myResult->source = 'collection';
      $myResult->topLabel = 'Special Collection';
    }
    else {
      $myResult->topLabel = 'Article';
    }
    $primoResponse = $this->client->send($primoRequest);
    $primoJson = $primoResponse->json();

    # How many hits did we get?
    $myResult->total = $primoJson['info']['total'];
    $myResult->plural = $this->isPlural($myResult->total);

    $sentData = array();

	# Process hits
	foreach ($primoJson['docs'] as $docs) {
        $implodedCreator = (is_array($docs['creator'])) ? implode(', ', $docs['creator']) : $docs['creator'];

	    $sentData['my_title'] = $docs['title'] ? $docs['title'] : 'No Title information available.';
	    $sentData['my_link']  = "https://ou-primo.hosted.exlibrisgroup.com/primo-explore/fulldisplay?docid=".$docs['pnxId']."&vid=".$this->vid."";
        $sentData['date'] = $docs['date'] ? $docs['date'] : 'No published date information available.';
        $sentData['subjects'] = FALSE;
        $sentData['creator'] = $implodedCreator ? $implodedCreator : 'No creator information available.';
        $sentData['type'] = $docs['type'] ? ($docs['type'] == 'book') ? FALSE : $docs['type'] :'No type information available.';


	    $myResult->addHit($sentData);
	}

    return $myResult;
  }

}