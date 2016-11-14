<?php

namespace SearchGateway\Model;

Class Result {
 
    public $source ="";
    public $query ="";
    public $full ="";
    public $hits = q[];
    public $total =0 ;


    public function __construct() {
	;
    }



    public function getData() {

	$data = [];

	$data["source"] = $this->source;
	$data["query"] = $this->query;
	$data["full"] = $this->full;
	$data["hits"] = $this->hits;
	$data["total"] = $this->total;

	return $data;
    }



  }