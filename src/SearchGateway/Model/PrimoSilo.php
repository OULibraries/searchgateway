<?php
namespace SearchGateway\Model;

/*
 * PrimoSilo - interface with Primo search backend. 
 *
 */
Class PrimoSilo extends Silo {

  public function __construct($primoHost, $primoKey, $primoBook, $vid) {
    parent::__construct();
    $this->primoHost = $primoHost;
    $this->primoKey = $primoKey;
    $this->primoBook = $primoBook;
    $this->vid = $vid;
  }

  /*
   * Get a Result from Primo
   */
  public function getResult($query, $limit) {

    #only set this is it is for 'books only'
    $bookSearchArg = ($this->primoBook) ? ',AND&pfilter=pfilter,exact,books,AND&mode=advanced' : '';

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
    if ($this->primoBook) {
      $primoQuery['qInclude'] = 'facet_rtype,exact,books';
    }
    $primoResponse = $this->client->send($primoRequest);
    $primoJson = $primoResponse->json();

    # How many hits did we get?
    $myResult->total = $primoJson['info']['total'];

	# Process hits
	foreach ($primoJson['docs'] as $docs) {
        #if there is more than one subject it is returned as an array
        #if it is we separate each one by a vertical bar '|'
        $implodedSubs = (is_array($docs['subject'])) ? implode(' | ', $docs['subject']) : $docs['subject'];

	    $my_title = $docs['title'];
	    $my_link  = "https://ou-primo.hosted.exlibrisgroup.com/primo-explore/fulldisplay?docid=".$docs['pnxId']."&vid=".$this->vid."";
	    $my_description  = $implodedSubs;

	    $myResult->addHit($my_link, $my_title, $my_description);
	}

    return $myResult;
  }

}