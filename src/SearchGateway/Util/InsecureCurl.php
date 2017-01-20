<?php namespace SearchGateway\Util;

class InsecureCurl extends \Solarium\Core\Client\Adapter\Curl 
{
    public function createHandle($request, $endpoint)
    {
        $handler = parent::createHandle($request, $endpoint);

        curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);

        return $handler;
    }
}