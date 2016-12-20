<?php
namespace SearchGateway\Model;

/*
 * PrimoSilo - interface with Primo search backend. 
 *
 */
Class PrimoSilo extends Silo {

  public function __construct($primoHost, $primoKey, $vid, $option) {
    parent::__construct();
    $this->primoHost = $primoHost;
    $this->primoKey = $primoKey;
    $this->primoOption = $option;
    $this->vid = $vid;
    $this->collections = array();
  }

  /*
   * Get a Result from Primo
   */
  public function getResult($query, $limit) {
    $this->collections = ['Bass Collection', 'Boorstin Collection'];

    #only set this is it is for 'books only'
    $bookSearchArg = ($this->primoOption != 'default') ? '&mode=advanced' : '';

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
    switch ($this->primoOption) {
      case 'books':
        $primoQuery['qInclude'] = 'facet_rtype,exact,books';
        $myResult->source = "primobooks";
        $myResult->topLabel = 'Book';
        break;
      case 'collection':
        $primoQuery['qInclude'] = 'facet_local6,exact,special_collections';
        $myResult->source = 'collection';
        $myResult->topLabel = 'Special Collection';
        break;
      case 'share':
        $primoQuery['scope'] = 'ou_dspace';
        $myResult->source = 'share';
        $myResult->topLabel = 'SHAREOK Article';
        break;
      default:
        $myResult->topLabel = 'Article';
        break;
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