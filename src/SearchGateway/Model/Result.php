<?php

namespace SearchGateway\Model;

Class Result {
 
    public $source ="";
    public $query ="";
    public $full ="";
    public $hits = []; // [{link, url, description}]
    public $total =0 ;

    public function __construct() {
	;
    }


    /**
     * Returns a dictionary representing a search result, suitable for
     * feeding to a JsonResponse
     */
    public function getData() {

	$data = [];
	$data["source"] = $this->source;
	$data["query"] = $this->query;
	$data["full"] = $this->full;
	$data["hits"] = $this->hits;
	$data["total"] = $this->total;

	return $data;
    }

    public function addHit( $link, $title, $description) {
	$this->hits[] =  [ 'link' => $link,
			   'title' => $title,
			   'text' => $description];
    }

    



  }