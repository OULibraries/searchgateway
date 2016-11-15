<?php

namespace SearchGateway\Model;

  /* 
   *Base clase for search Silos. 
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


