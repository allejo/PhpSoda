<?php

namespace allejo\Socrata;

class UrlQuery
{
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

    public function sendGet($params = array())
    {
        $full_url = self::buildQuery($this->url, $params);

        curl_setopt($this->cURL, CURLOPT_URL, $full_url);

        $this->handleQuery();
    }

    public function sendPost($data_as_json)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data_as_json,
            CURLOPT_CUSTOMREQUEST => "POST"
        ));

        $this->handleQuery();
    }

    public function sendPut($data_as_json)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_POSTFIELDS => $data_as_json,
            CURLOPT_CUSTOMREQUEST => "PUT"
        ));

        $this->handleQuery();
    }

    public function handleQuery()
    {
        $result = curl_exec($this->cURL);

        if (!$result)
        {
            throw new Exception("cURL Error " . curl_errno($this->cURL) . ": " curl_error($this->cURL), 1);
        }

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($httpCode != "200")
        {
            throw new Exception("HTTP Error " . $httpCode . ": " . $result, 1);
        }

        return json_decode($result);
    }

    public static function buildQuery($url, $params = array())
    {
        $full_url = $url;

        if (count($this->parameters) > 0)
        {
            $full_url .= "?" . implode("&", $this->parameters);
        }

        return $full_url;
    }
}