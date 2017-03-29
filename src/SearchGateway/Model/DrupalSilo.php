<?php
namespace SearchGateway\Model;
/**
 * Silo -  base clase for search backend interfaces
 *
 * Each search backend should be defined as a subclass of Silo, with
 * common functionality extracted to this base class as it emerges.
 */
class DrupalSilo extends Silo  {

  public function __construct( $conf, $option) {
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
    $this->option = $option;
  }

  public function getResult ( $needle, $limit) {

    $myResult = new Result();
    $myResult->source = "web";
    $myResult->query = $needle;


    // Setup Curl Connection and allow insecure certs
    $client = new \Solarium\Client($this->config);
    $client->setAdapter('\SearchGateway\Util\InsecureCurl');

    $selectOpts = array(
      "query" => $needle,
      "fields" => array("id","entity_id","entity_type","bundle",
                "bundle_name","label","ss_language","is_comment_count",
                "ds_created","ds_changed","score","path","url","is_uid",
                "tos_name","hash","site", "sm_field_one_sentence_teaser",
                "ts_title", "sm_picture" ),

    );

    $query = $client->createSelect($selectOpts);

    // enable EDisMax query parsing and match the Drupal search settings
    $edismax = $query->getEDisMax();
    $edismax->setMinimumMatch("1");
    $edismax->setPhraseFields("content^2.0");
    $edismax->setQueryFields(
      "content^40 label^5.0 tags_h1^5.0 tags_h2_h3^3.0 "
      ."tags_h4_h5_h6^2.0 tags_inline^1.0 taxonomy_names^2.0 "
      ."tos_content_extra^0.1 tos_name^3.0"
    );

    $textField="sm_field_one_sentence_teaser";

    // Which kind of thing are we searching for?
    switch ($this->option) {
      case "eresource":
        // Show only eresources
        $query->createFilterQuery('onlyE')->setQuery('+bundle:eresources');
        $myResult->full = $this->drupal_base."/search/eresources/".$needle;
        break;

      case "people":
        // Show only people with titles
        $query->createFilterQuery('onlyUsers')->setQuery('+bundle:user AND ts_title:*');
        $textField="ts_title";
        $myResult->full = $this->drupal_base."/search/research-specialists/".$needle;
        break;

      default:
        // Hide eresources and people from web search
        $query->createFilterQuery('hideE')->setQuery('-bundle:eresources');
        $query->createFilterQuery('hideUsers')->setQuery('-bundle:user');
        $myResult->full = $this->drupal_base."/search/site-pages/".$needle;
    }

    $query->setQuery($needle);
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
        $sentData['text'] = $doc->$textField;
        $sentData['type'] = $this->_getType($doc->bundle);
        $sentData['image'] = $doc->sm_picture[0];
        $myResult->addHit($sentData);
      }
    return $myResult;
  }

  private function _getType( $bundle) {
    $resultType="web page";
    if( "event" == $bundle) {
      $resultType = "event";
    }
    return $resultType;
  }

}
