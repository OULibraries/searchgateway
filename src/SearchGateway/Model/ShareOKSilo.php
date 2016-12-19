<?php
/**
 * Created by PhpStorm.
 * User: smit0015
 * Date: 12/13/2016
 * Time: 12:57 PM
 */

namespace SearchGateway\Model;

/*
 * PrimoSilo - interface with Primo search backend.
 *
 */
Class ShareOKSilo extends Silo {

  public function __construct($primoHost, $primoKey, $vid) {
    parent::__construct();
    $this->primoHost = $primoHost;
    $this->primoKey = $primoKey;
    $this->vid = $vid;
  }

  /*
   * Get a Result from Primo
   */
  public function getResult($query, $limit) {

    $myResult = new Result();
    $myResult->source = "primo";
    $myResult->query = $query;
    $myResult->full = "//ou-primo.hosted.exlibrisgroup.com/primo-explore/search?query=any,contains," . $query . "&search_scope=default_scope&vid=" . $this->vid . "&sortby=rank";


    # Do primo search
    # See API docs
    # https://developers.exlibrisgroup.com/primo/apis/webservices/rest/pnxs
    $primoRequest = $this->client->createRequest('GET', $this->primoHost . "/primo/v1/pnxs");
    $primoQuery = $primoRequest->getQuery();
    $primoQuery['q'] = 'any,contains,' . $query; //the search term
    $primoQuery['limit'] = $limit; //number of records to return
    $primoQuery['apikey'] = $this->primoKey; //private key for OU
    $primoQuery['vid'] = $this->vid; //identification code...right now it is 'OUNEW'
    $primoQuery['scope'] = 'ou_dspace'; //range of types to return...default is everything
    $primoQuery['addfields'] = ['pnxId']; //specific identifier for individual records
    $primoQuery['view'] = 'full'; //view = full will return everything...including the subject or description

    $primoResponse = $this->client->send($primoRequest);
    $primoJson = $primoResponse->json();

    # How many hits did we get?
    $myResult->total = $primoJson['info']['total'];
    $myResult->plural = $this->isPlural($myResult->total);
    $myResult->topLabel = 'SHAREOK Article';

    $sentData = array();

    # Process hits
    foreach ($primoJson['docs'] as $docs) {
      $implodedCreator = (is_array($docs['creator'])) ? implode(', ', $docs['creator']) : $docs['creator'];

      $sentData['my_title'] = $docs['title'] ? $docs['title'] : 'No Title information available.';
      $sentData['my_link']  = "https://ou-primo.hosted.exlibrisgroup.com/primo-explore/fulldisplay?docid=".$docs['pnxId']."&vid=".$this->vid."";
      $sentData['date'] = $docs['date'] ? $docs['date'] : 'No published date information available.';
      $sentData['subjects'] = FALSE;
      $sentData['creator'] = $implodedCreator ? $implodedCreator : 'No creator information available.';
      $sentData['type'] = $docs['type'] ? $docs['type'] :'No type information available.';


      $myResult->addHit($sentData);
    }

    return $myResult;
  }

}