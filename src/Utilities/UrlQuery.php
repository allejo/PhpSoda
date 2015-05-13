<?php

namespace allejo\Socrata\Utilities;

use allejo\Socrata\Exceptions\CurlException;
use allejo\Socrata\Exceptions\HttpException;

class UrlQuery
{
    const DefaultProtocol = "https";

    private $url;
    private $cURL;
    private $token;
    private $parameters;

    public function __construct ($url, $token)
    {
        $this->url   = $url;
        $this->token = $token;
        $this->cURL  = curl_init();

        // Build up the headers we'll need to pass
        $headers = array(
            'Accept: application/json',
            'Content-type: application/json',
            "X-App-Token: " . $this->token
        );

        curl_setopt_array($this->cURL, array(
            CURLOPT_URL => $this->url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true
        ));
    }

    public function __destruct()
    {
        curl_close($this->cURL);
    }

    public function setAuthentication($username, $password)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $username . ":" . $password
        ));
    }

    public function setParameters($params)
    {
        $this->parameters = array();

        foreach ($params as $key => $value)
        {
            $this->parameters[] = urlencode($key) . "=" . urlencode($value);
        }
    }

    public function sendGet($params)
    {
        if (is_array($params))
        {
            $full_url = self::buildQuery($this->url, $params);
        }
        else
        {
            $full_url = $this->url . "?" . $params;
        }

        curl_setopt($this->cURL, CURLOPT_URL, $full_url);

        return $this->handleQuery();
    }

    public function sendPost($data_as_json)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data_as_json,
            CURLOPT_CUSTOMREQUEST => "POST"
        ));

        return $this->handleQuery();
    }

    public function sendPut($data_as_json)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_POSTFIELDS => $data_as_json,
            CURLOPT_CUSTOMREQUEST => "PUT"
        ));

        return $this->handleQuery();
    }

    public function handleQuery()
    {
        $result = curl_exec($this->cURL);

        if (!$result)
        {
            throw new CurlException(curl_errno($this->cURL), curl_error($this->cURL));
        }

        $httpCode = curl_getinfo($this->cURL, CURLINFO_HTTP_CODE);

        if ($httpCode != "200")
        {
            throw new HttpException($httpCode, $result);
        }

        return json_decode($result);
    }

    public static function buildQuery($url, $params = array())
    {
        $full_url = $url;

        if (count($params) > 0)
        {
            $full_url .= "?" . implode("&", $params);
        }

        return $full_url;
    }
}