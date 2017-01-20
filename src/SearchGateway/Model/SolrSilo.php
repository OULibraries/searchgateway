<?php
namespace SearchGateway\Model;
/**
 * Silo -  base clase for search backend interfaces
 *
 * Each search backend should be defined as a subclass of Silo, with
 * common functionality extracted to this base class as it emerges.
 */
Class SolrSilo extends Silo  {

    public function __construct($conf) 
    {
        $this->config = array(
            "endpoint" => array(
                "localhost" => array(
                    "scheme"  => "https",
                    "host"    => $conf['solr_host'],
                    "port"    => 443,
                    "username"=> $conf['solr_user'],
                    "password"=> $conf['solr_pass'],
                    "path"    => $conf['solr_path'], 
                    "core"    => $conf['solr_core'],
                ) ) );
        $this->drupal_base = $conf['solr_drupal'];

    }

    public function getResult ( $query, $limit){

        $myResult = new Result();
        $myResult->source = "web";
        $myResult->query = $query;
        $myResult->full = $this->drupal_base."/".$query;

        // Setup Curl Connection and allow insecure certs
        $client = new \Solarium\Client($this->config);
        $client->setAdapter('\SearchGateway\Util\InsecureCurl');

        // Try to match Drupal's query settings
        $selectOpts=array(
            "minimummatch" => "1"
        );
        $query = $client->createSelect($selectOpts);
        $edismax = $query->getEDisMax();

        $query->setQuery($query);
        $query->setRows($limit);
        $resultSet = $client->select($query);

        $myResult->total = $resultSet->getNumFound();
        $myResult->plural = $this->isPlural($myResult->total);
        $myResult->topLabel = 'Page';

        foreach( $resultSet as $doc)
        {
            $sentData = array();
            $sentData['my_title'] = $doc->label;
            $sentData['my_link']  = $doc->url;
            $sentData['subjects'] = $doc->teaser;
            $sentData['type'] = "website";
            $myResult->addHit($sentData);
        }
        return $myResult;
    }
}


