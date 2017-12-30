<?php

use Codebird\Codebird;

class TwitterAuth {
  protected $client;
  protected $oauth_callback = TWITTER_REDIRECT_URI;
  protected $payload;

  public function __construct(Codebird $twitter_client, string $auth_type = 'login') {
    $this->client = $twitter_client;

    if ($this->client) {
      $api_keys = $this->getApiKeys();

      $this->client::setConsumerKey($api_keys['api_key'], $api_keys['api_secret']);

      if ($auth_type == 'register') {
        $this->oauth_callback = TWITTER_REDIRECT_URI_REGISTER;
      }
    }
  }

  public function getAuthUrl() {
    $this->requestTokens();
    $this->verifyTokens();

    return filter_var($this->client->oauth_authorize(), FILTER_SANITIZE_URL);
  }

  public function checkCallback(string $verifier) {
    if (!empty($verifier)) {
      $this->verifyTokens();
      unset($_SESSION['oauth_verify']);

      $reply = $this->client->oauth_accessToken([
        'oauth_verifier' => $verifier
      ]);

      if ($reply->httpstatus == 200) {
        $this->storeTokens($reply->oauth_token, $reply->oauth_token_secret);

        $this->verifyTokens();
        $this->setPayload($this->requestPayload());

        return [
          'oauth_token' => $_SESSION['oauth_token'],
          'oauth_token_secret' => $_SESSION['oauth_token_secret']
        ];
      }
    }

    return false;
  }

  public function getPayload() {
    if (!empty($this->payload)) {
      return $this->payload;
    }

    return false;
  }

  public function logout() {
    $this->client->logout();
  }

  protected function getApiKeys() {
    $api_keys = json_decode(file_get_contents(TWITTER_AUTH_CONFIG_FILE, true), true);
    
    if (!empty($api_keys)) {
      return $api_keys;
    }

    return false;
  }

  protected function requestTokens() {
    $reply = $this->client->oauth_requestToken([
      'oauth_callback' => $this->oauth_callback
    ]);

    $this->storeTokens($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_verify'] = true;
  }

  protected function storeTokens(string $token, string $token_secret) {
    $_SESSION['oauth_token'] = $token;
    $_SESSION['oauth_token_secret'] = $token_secret;
  }

  protected function verifyTokens() {
    $this->client->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
  }

  protected function requestPayload() {
    return $this->client->account_verifyCredentials([
      'include_email' => true
    ]);
  }

  protected function setPayload($reply) {
    $this->payload = [
      'user_id' => $reply->id,
      'email' => $reply->email
    ];
  }
}
