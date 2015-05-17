<?php

namespace allejo\Socrata\Utilities;

use allejo\Socrata\Exceptions\CurlException;
use allejo\Socrata\Exceptions\HttpException;

class UrlQuery
{
    const DEFAULT_PROTOCOL = "https";

    private $url;
    private $cURL;
    private $token;
    private $parameters;

    public function __construct ($url, $token = "", $email = "", $password = "")
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
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true
        ));

        if (!StringUtilities::isNullOrEmpty($email) && !StringUtilities::isNullOrEmpty($password))
        {
            curl_setopt_array($this->cURL, array(
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $email . ":" . $password
            ));
        }
    }

    public function __destruct ()
    {
        curl_close($this->cURL);
    }

    public function setParameters ($params)
    {
        $this->parameters = array();

        foreach ($params as $key => $value)
        {
            $this->parameters[] = urlencode($key) . "=" . urlencode($value);
        }
    }

    public function sendGet ($params, $associativeArray, &$headers = null)
    {
        if (is_array($params))
        {
            $full_url = self::buildQuery($this->url, $params);
        }
        else if (!empty($params))
        {
            $full_url = $this->url . "?" . $params;
        }
        else
        {
            $full_url = $this->url;
        }

        curl_setopt($this->cURL, CURLOPT_URL, $full_url);

        return $this->handleQuery($associativeArray, $headers);
    }

    public function sendPost ($dataAsJson, $associativeArray, &$headers = null)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $dataAsJson,
            CURLOPT_CUSTOMREQUEST => "POST"
        ));

        return $this->handleQuery($associativeArray, $headers);
    }

    public function sendPut ($dataAsJson, $associativeArray, &$headers = null)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_POSTFIELDS => $dataAsJson,
            CURLOPT_CUSTOMREQUEST => "PUT"
        ));

        return $this->handleQuery($associativeArray, $headers);
    }

    private function handleQuery ($associativeArray, &$headers)
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

        list($header, $body) = explode("\r\n\r\n", $result, 2);

        if ($headers !== null)
        {
            $header = explode("\r\n", $header);
            $headers = array();

            for ($i = 1; $i < count($header); $i++)
            {
                list($k, $v) = explode(":", $header[$i]);
                $headers[$k] = trim($v);
            }
        }

        return json_decode($body, $associativeArray);
    }

    public static function buildQuery ($url, $params = array())
    {
        $full_url = $url;

        if (count($params) > 0)
        {
            $full_url .= "?" . implode("&", $params);
        }

        return $full_url;
    }
}
