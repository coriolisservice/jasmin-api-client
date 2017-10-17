<?php

namespace Coriolis\Jasmin;

use GuzzleHttp\Client;

/**
*  A sample class
*
*  Use this section to define what this class is doing, the PHPDocumentator will use this
*  to automatically generate an API documentation using this information.
*
*  @author yourname
*/
class ApiClient
{
    const SEND_SMS_MODE = 'HTTP'; // HTTP ou REST
    /** @var string */
    private $base_uri;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    
    /** @var boolean */
    private $dlr = false;
    /** @var int */
    private $dlr_level = 3;    
    /** @var string */
    private $dlr_url;
    /** @var string */
    private $dlr_method = 'GET';
    
    /** @var array */
    private $parameters;
    
    /**
     * 
     * @param string $base_uri
     * @param string $username
     * @param string $password
     */
    public function __construct($base_uri, $username, $password, $dlr = false, $dlr_level = null, $dlr_url = null, $dlr_method = null) 
    {
        $this->base_uri = $base_uri;
        $this->username = $username;
        $this->password = $password;
        // dlr informations
        $this->dlr = $dlr;
        $this->dlr_level = $dlr_level;
        $this->dlr_url = $dlr_url;
        $this->dlr_method = $dlr_method;
    }
    
    /**
     * 
     * @param string $phone_number
     * @param string $message
     * @param string $transmitter
     * @return string
     * @throws \Exception
     */
    public function sendSms($phone_number, $message, $transmitter = null)
    {
        if (self::SEND_SMS_MODE == 'HTTP' || self::SEND_SMS_MODE == 'REST') {
            $this->setParameters($phone_number, $message, $transmitter);
            if (self::SEND_SMS_MODE == 'HTTP') {
                return $this->sendSmsHttpApi();
            } elseif (self::SEND_SMS_MODE == 'REST') {
                return $this->sendSmsRestApi();
            }
        } else {
            throw new \Exception ('Send SMS mode is not correctly set');
        }
    }
    
    /**
     * 
     * @param string $phone_number
     * @param string $message
     * @param string $transmitter
     */
    private function setParameters($phone_number, $message, $transmitter) 
    {
        $this->parameters = [
            'username' => $this->username,
            'password' => $this->password,
            'to' => $phone_number,
            'from' => $transmitter,
            'content' => $message,
        ];
        // add dlr parameters
        if ($this->dlr === true && in_array($this->dlr_level, [1, 2, 3]) && in_array($this->dlr_method, ['GET', 'POST']) && $this->dlr_url != '') {
            $this->parameters += [
                'dlr' => 'yes',
                'dlr-url' => $this->dlr_url,
                'dlr-level' => $this->dlr_level,
                'dlr-method' => $this->dlr_method,
            ];
        }
    }
    
    /**
     * 
     * @return array
     */
    private function getParameters()
    {
        return $this->parameters;
    }
    
    /**
     * Send SMS through Jasmin HTTP API
     * @return string
     * @throws \Exception
     */
    public function sendSmsHttpApi()
    {
        try {
            $client = new Client(['base_uri' => $this->base_uri]);
            $response = $client->get('/send', [
                'debug' => TRUE,
                'query' => $this->getParameters(),
            ]);

            if ($response->getStatusCode() == 200) {
                // body is like Success "07033084-5cfd-4812-90a4-e4d24ffb6e3d"
                $messageId = substr($response->getBody(), 9, strlen($response->getBody()) - 9 - 1);
            } else {
                throw new \Exception('Non-handled exception : return status code is not 200');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
        return $messageId;
    }  
    
    /**
     * Send SMS through Jasmin REST API (not working)
     * @return string
     * @throws \Exception
     */
    public function sendSmsRestApi()
    {
        try {
            $client = new Client(['base_uri' => $this->base_uri]);
            $response = $client->post('/secure/send/', [
                'debug' => TRUE,
                'auth' => [$this->username, $this->password],
                'json' => $this->getParameters(),
            ]);
            if ($response->getStatusCode() == 200) {
                // body is like Success "07033084-5cfd-4812-90a4-e4d24ffb6e3d"
                $messageId = substr($response->getBody(), 9, strlen($response->getBody()) - 9 - 1);
            } else {
                throw new \Exception('Non-handled exception : return status code is not 200');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
        return $messageId;
    }  
    
}