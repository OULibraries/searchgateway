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
  public $plural = []; // The word 'All' and the 's' if plural
  public $topLabel = '';
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
    $data["total"] = number_format($this->total);
    $data['plural'] = $this->plural;
    $data['topLabel'] = $this->topLabel;
    $data["hits"] = $this->hits;
    return $data;
  }

  /**
   * Adds a ($link, $title, $description) tuple to the list of hits
   * in this search result.
   */
  public function addHit($sentData) {
    $this->hits[] =  [
      'link' => $sentData['my_link'],
      'title' => $sentData['my_title'],
      'text' => $sentData['subjects'],
      'date' => $sentData['date'],
      'creator' => $sentData['creator'],
      'image' => $sentData['image'],
      'type' => $sentData['type']
    ];
  }
}