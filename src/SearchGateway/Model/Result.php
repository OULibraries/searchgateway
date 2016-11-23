<?php

namespace SearchGateway\Model;

/**
 * Defines a simple object to represents a search result. 
 */
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

    /**
     * Adds a ($link, $title, $description) tuple to the list of hits
     * in this search result.
     */
    public function addHit( $link, $title, $description) {
	$this->hits[] =  [ 'link' => $link,
			   'title' => $title,
			   'text' => $description];
    }
}