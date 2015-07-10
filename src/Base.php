<?php

/**
 * @file
 * Base abstract class for AppleNews classes.
 */

namespace ChapterThree\AppleNews;

/**
 * Base abstract class for AppleNews classes.
 */
abstract class Base {

  // PushAPI API Key ID.
  public $api_key_id = '';

  // Push API Secret Key.
  public $api_key_secret = '';

  // PushAPI Endpoint URL.
  public $endpoint = '';

  // HTTP client class.
  public $http_client;

  // Endpoint path.
  protected $path = '';

  // HTTP Method (GET/DELETE/POST).
  protected $method = '';

  // Endpoint path variables to replace.
  protected $path_args = [];

  // ISO 8601 datetime.
  protected $datetime;

  /**
   * Initialize variables needed in the communication with the API.
   *
   * @param string $key
   *   API Key.
   * @param string $secret
   *   API Secret Key.
   * @param string $endpoint
   *   API endpoint URL.
   */
  public function __construct($key, $secret, $endpoint) {
    // Set API required variables.
    $this->api_key_id = $key;
    $this->api_key_secret = $secret;
    $this->endpoint = $endpoint;
    // PHP Curl Class.
    $this->http_client = new \Curl\Curl;
    // ISO 8601 date and time format.
    $this->datetime = gmdate(\DateTime::ISO8601);
  }

  /**
   * Generate HMAC cryptographic hash.
   *
   * @param string $data
   *   Message to be hashed.
   *
   * @return string
   *   Authorization token used in the HTTP headers.
   */
  protected function HHMAC($data) {
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $data, $key, true);
    $encoded = base64_encode($hashed);
    $signature = rtrim($encoded, "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s',
      $this->api_key_id, strval($signature),
      $this->datetime
    );
  }

  /**
   * Create canonical version of the request as a byte-wise concatenation.
   *
   * @param string $string
   *   String to concatenate (see POST method).
   *
   * @return string
   *   HMAC cryptographic hash
   */
  protected function Authentication($string = '') {
    $data = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $string;
    return $this->HHMAC($data);
  }

  /**
   * Generate URL to request.
   *
   * @return string
   *   URL to create request.
   */
  protected function Path() {
    $params = array();
    // Take arguments and pass them to the path by replacing {argument} tokens.
    foreach ($this->path_args as $argument => $value) {
      $params["{{$argument}}"] = $value;
    }
    $path = str_replace(array_keys($params), array_values($params), $this->path);
    return $this->endpoint . $path;
  }

  /**
   * Make PreprocessData method required.
   */
  abstract protected function PreprocessData($method, $path, Array $path_args, Array $vars);

  /**
   * Set HTTP headers.
   *
   * @param array $headers
   *   Associative array [header field name => value].
   */
  protected function SetHeaders(Array $headers = []) {
    foreach ($headers as $property => $value) {
      $this->http_client->setHeader($property, $value);
    }
  }

  /**
   * Remove specified header names from HTTP request.
   *
   * @param array $headers
   *   Associative array [header1, header2, ..., headerN].
   */
  protected function UnsetHeaders(Array $headers = []) {
    foreach ($headers as $property) {
      $this->http_client->unsetHeader($property);
    }
  }

  /**
   * Create HTTP request.
   *
   * @param mixed $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Structured object.
   */
  protected function Request($data) {
    $response = $this->http_client->{$this->method}($this->Path(), $data);
    $this->http_client->close();
    return $this->Response($response);
  }

  /**
   * Preprocess HTTP response.
   *
   * @param object $response
   *   Structured object.
   *
   * @return object
   *   Preprocessed structured object.
   */
  protected function Response($response) {
    return $response;
  }

  /**
   * Sets an option on the given cURL session handle.
   * 
   * @param string $name
   *   The CURLOPT_XXX option to set.
   * @param string $value
   *   The value to be set on option.
   */
  public function SetOption($name, $value) {
    $this->http_client->setOpt($name, $value);
  }

  /**
   * Create GET request to a specified endpoint.
   *
   * @param string $path
   *   Path to API endpoint.
   * @param string $path_args
   *   Endpoint path arguments to replace tokens in the path.
   * @param string $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Preprocessed structured object.
   */
  abstract public function Get($path, Array $path_args, Array $data);

  /**
   * Create POST request to a specified endpoint.
   *
   * @param string $path
   *   Path to API endpoint.
   * @param string $path_args
   *   Endpoint path arguments to replace tokens in the path.
   * @param string $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Preprocessed structured object.
   */
  abstract public function Post($path, Array $path_args, Array $data);

  /**
   * Open and load file information and prepare data for multipart data.
   *
   * @param string $path
   *   Path to a file included in the POST request.
   *
   * @return array
   *   Associative array. The array contains information about a file.
   */
  abstract public function Delete($path, Array $path_args, Array $data);

  /**
   * Implements __get().
   */
  public function __get($name) {
    return $this->$name;
  }

  /**
   * Implements __set().
   *
   * Intended to be overridden by subclass.
   */
  public function __set($name, $value) {
    $this->triggerError('Undefined property via __set(): ' . $name);
    return NULL;
  }

  /**
   * Implements __isset().
   */
  public function __isset($name) {
    return isset($this->$name);
  }

  /**
   * Implements __unset().
   */
  public function __unset($name) {
    unset($this->$name);
  }

  /**
   * Error handler.
   */
  public function triggerError($message, $message_type = E_USER_NOTICE) {
    $trace = debug_backtrace();
    trigger_error($message . ' in ' . $trace[0]['file'] . ' on line ' .
      $trace[0]['line'], $message_type);
  }

}