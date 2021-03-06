<?php

/**
 * Abstract API client
 * @author Ben Tadiar <ben@handcraftedbyben.co.uk>
 * @package MailChimp
 * @subpackage Client
 */
namespace MailChimp\Client;

abstract class ClientAbstract
{
    /**
     * MailChimp API version
     * @var float
     */
    private $version = 1.3;
    
    /**
     * MailChimp API Endpoint
     * @var string
     */
    private $endpoint = '%s://%s.api.mailchimp.com/%s/';
    
    /**
     * Use SSL Endpoint
     * Ignored if OpenSSL is not loaded
     * @var boolean Default: true
     */
    private $useSSL = true;
    
    /**
     * User API key
     * @var string
     */
    private $key = null;
    
    /**
     * Associative array of JSON related errors
     * @var array
     */
    private $jsonErrors = array(
        JSON_ERROR_NONE           => 'No error has occurred',
        JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX         => 'Syntax error',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    );
    
    /**
     * Format the API endpoint and set the API key
     * @param string $key User API key
     */
    protected function __construct($key)
    {
        // Set the API endpoint
        $this->key = $key;
        $prefix = substr($this->key, strpos($this->key, '-') + 1);
        $protocol = ($this->useSSL && extension_loaded('OpenSSL')) ? 'https' : 'http';
        $this->endpoint = sprintf($this->endpoint, $protocol, $prefix, $this->version);
    }
    
    /**
     * Prepare an API request
     * @param string $method API method
     * @param array $params Associative array of request parameters
     * @return string API request URL
     */
    public function prepare($method, $params)
    {
        // Define the default parameters
        $default = array(
            'apikey' => $this->key,
            'method' => $method,
            'output' => 'json',
        );
        
        // Merge the request parameters with the defaults
        $params = array_merge($default, $params);
        
        // Build the request URL
        $query = '?' . http_build_query($params, '', '&');
        $url = $this->endpoint . $query;
        
        // Return the prepared URL
        return $url;
    }
    
    /**
     * Parse an API response
     * @param string $response
     * @throws \MailChimp\Exception
     * @return stdClass
     */
    protected function parse($response)
    {
        if (!$response = json_decode($response)) {
            $error = json_last_error();
            $message = 'Unable to decode response. Error: ' . $this->jsonErrors[$error];
            throw new \MailChimp\Exception($message);
        } elseif (isset($response->error)) {
            throw new \MailChimp\Exception($response->error, $response->code);
        } else {
            return $response;
        }
    }
}
