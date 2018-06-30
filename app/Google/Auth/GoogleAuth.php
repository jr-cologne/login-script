<?php

namespace LoginScript\Google\Auth;

use LoginScript\{
  Config\Config,
  Session\Session
};

use \Google_Client;

class GoogleAuth {

  protected $client;

  public static function getAuthUrl(string $auth_type = 'login') : string {
    $auth = new static($auth_type);

    return filter_var($auth->client->createAuthUrl(), FILTER_SANITIZE_URL);
  }

  public function __construct(string $auth_type = 'login') {
    $this->client = new Google_Client;

    $this->client->setAuthConfig(Config::get('social_auth/google/config_file'));
    $this->client->setIncludeGrantedScopes(true);
    $this->client->addScope(Config::get('social_auth/google/scopes'));

    if ($auth_type == 'register') {
      $this->client->setRedirectUri(Config::get('social_auth/google/redirect_uri/register'));
    } else {
      $this->client->setRedirectUri(Config::get('social_auth/google/redirect_uri/login'));
    }
  }

  public function checkRedirectCode(string $code) : string {
    if (!empty($code)) {
      $access_token = $this->client->fetchAccessTokenWithAuthCode($code)['access_token'] ?? '';

      return $access_token;
    }

    return '';
  }

  public function getPayload() {
    return $this->client->verifyIdToken();
  }

  public function logout($token) {
    $this->client->revokeToken($token);
  }
}
