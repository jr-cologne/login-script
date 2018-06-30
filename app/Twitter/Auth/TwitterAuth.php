<?php

namespace LoginScript\Twitter\Auth;

use LoginScript\{
  Twitter\Auth\Exception\TwitterAuthException,
  Config\Config,
  Session\Session
};

use \Codebird\Codebird;

class TwitterAuth {

  protected $client;

  protected $redirect_uri;

  protected $payload;

  public static function getAuthUrl(string $auth_type = 'login') : string {
    $auth = new static($auth_type);

    $auth->requestTokens();
    $auth->verifyTokens();

    return filter_var($auth->client->oauth_authorize(), FILTER_SANITIZE_URL);
  }

  public function __construct(string $auth_type = 'login') {
    $this->client = Codebird::getInstance();

    $api_keys = $this->getApiKeys();

    $this->client::setConsumerKey($api_keys['api_key'], $api_keys['api_secret']);

    if ($auth_type == 'register') {
      $this->redirect_uri = Config::get('social_auth/twitter/redirect_uri/register');
    } else {
      $this->redirect_uri = Config::get('social_auth/twitter/redirect_uri/login');
    }
  }

  public function checkCallback(string $verifier) : array {
    if (!empty($verifier)) {
      $this->verifyTokens();

      Session::delete('oauth_verify');

      $reply = $this->client->oauth_accessToken([
        'oauth_verifier' => $verifier
      ]);

      if ($reply->httpstatus == 200) {
        $this->storeTokens($reply->oauth_token, $reply->oauth_token_secret);

        $this->verifyTokens();

        $this->setPayload($this->requestPayload());

        $this->removeTokens();

        return [
          'oauth_token' => $reply->oauth_token ?? null,
          'oauth_token_secret' => $reply->oauth_token_secret ?? null
        ];
      }
    }

    return [];
  }

  public function getPayload() : array {
    if (!empty($this->payload)) {
      return $this->payload;
    }

    return [];
  }

  public function logout() {
    $this->client->logout();
  }

  protected function getApiKeys() : array {
    $config = Config::get('social_auth/twitter/config_file');

    if (is_string($config) && !file_exists($config)) {
      $api_keys = json_decode($config, true);
    } else {
      $api_keys = json_decode(file_get_contents($config, true), true);
    }

    $api_key = $api_keys['api_key'] ?? null;
    $api_secret = $api_keys['api_secret'] ?? null;

    if ( !$api_key || !$api_secret ) {
      throw new TwitterAuthException('Twitter API keys could not be retrieved');
    }

    return $api_keys;
  }

  protected function requestTokens() {
    $reply = $this->client->oauth_requestToken([
      'oauth_callback' => $this->redirect_uri
    ]);

    $this->storeTokens($reply->oauth_token, $reply->oauth_token_secret);

    Session::put('oauth_verify', true);
  }

  protected function storeTokens(string $token, string $token_secret) {
    Session::put('oauth_token', $token);
    Session::put('oauth_token_secret', $token_secret);
  }

  protected function verifyTokens() {
    $this->client->setToken(Session::get('oauth_token'), Session::get('oauth_token_secret'));
  }

  protected function removeTokens() {
    Session::delete('oauth_token');
    Session::delete('oauth_token_secret');
  }

  protected function requestPayload() {
    return $this->client->account_verifyCredentials([
      'include_email' => true
    ]);
  }

  protected function setPayload($payload) {
    $this->payload = [
      'user_id' => $payload->id ?? null,
      'email' => $payload->email ?? null
    ];
  }

}
