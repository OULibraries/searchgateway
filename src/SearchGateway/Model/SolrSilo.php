<?php
namespace SearchGateway\Model;
/**
 * Silo -  base clase for search backend interfaces
 *
 * Each search backend should be defined as a subclass of Silo, with
 * common functionality extracted to this base class as it emerges.
 */
Class SolrSilo  {

    public function __construct() {
        ;	   
    }

    public function getResult ( $query, $limit){

        $myResult = new Result();
        $myResult->source = "web";
        $myResult->query = $query;
        $myResult->full = "TBD";
	
        $config = array(
            "endpoint" => array(
                "localhost" => array(
                    "scheme"=>"https",
                    "host"=>"solr.vagrant.localdomain",
                    "port"=>443,
                    "username"=>"solr",
                    "password"=>"solr",
                    "path"=>"/solr", 
                    "core"=>"collection1",
                ) ) );

        $client = new \Solarium\Client($config);

        
        $myAdapter = new \SearchGateway\Util\InsecureCurl();


        $client->setAdapter($myAdapter);


        $query = $client->createSelect();





# /solr/select?
// start=0
// rows=10
// spellcheck=true
// q=christmas
// fl=id%2Centity_id%2Centity_type%2Cbundle%2Cbundle_name%2Clabel%2Css_language%2Cis_comment_count%2Cds_created%2Cds_changed%2Cscore%2Cpath%2Curl%2Cis_uid%2Ctos_name%2Chash%2Csite
// mm=1
// pf=content%5E2.0&ps=15
// hl=true
// hl.fl=content
// hl.snippets=3
// hl.mergeContigious=true
// f.content.hl.alternateField=teaser
// f.content.hl.maxAlternateFieldLength=256
// spellcheck.q=christmas
// qf=content%5E40
// qf=label%5E5.0
// qf=tags_h1%5E5.0
// qf=tags_h2_h3%5E3.0
// qf=tags_h4_h5_h6%5E2.0
// qf=tags_inline%5E1.0
// qf=taxonomy_names%5E2.0
// qf=tos_content_extra%5E0.1
// qf=tos_name%5E3.0
// debugQuery=on
// wt=json
// json.nl=map



        $query->setQuery($query);


        $resultSet = $client->select($query);


        $myResult->total = $resultSet->getNumFound();

        foreach( $resultSet as $doc)
        {

            $sentData = array();
            $sentData['my_title'] = $doc->label;
            $sentData['my_link']  = $doc->url;
            $sentData['type'] = $doc->bundle;
            $myResult->addHit($sentData);
        }
        
        return $myResult;
    }

}


