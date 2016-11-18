<?php

namespace SearchGateway\Model;

Class Result {
 
    public $source ="";
    public $query ="";
    public $full ="";
    public $total =0 ;
    public $hits = []; // [{link, url, description}]

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
    $data["total"] = $this->total;
	$data["hits"] = $this->hits;

	return $data;
    }

    public function addHit( $link, $title, $description) {
	$this->hits[] =  [ 'link' => $link,
			   'title' => $title,
			   'text' => $description];
    }

    



  }