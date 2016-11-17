<?php
namespace SearchGateway\Model;
/**
 * Silo -  base clase for search backend interfaces
 *
 * Each search backend should be defined as a subclass of Silo, with
 * common functionality extracted to this base class as it emerges.
 */
Class Silo  {

    public function __construct() {
	# Setup web client
	$this->client = new \GuzzleHttp\Client();
	$this->jar = new \GuzzleHttp\Cookie\CookieJar();
    }

    public function getResult ( $query, $limit){
	;
    }

}


