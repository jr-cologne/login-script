<?php
  class GoogleAuth {
    protected $client;

    public function __construct(Google_Client $google_client=null, string $auth_type='login') {
      $this->client = $google_client;

      if ($this->client) {
        $this->client->setAuthConfig(GOOGLE_AUTH_CONFIG_FILE);
        $this->client->setIncludeGrantedScopes(true);
        $this->client->addScope(GOOGLE_SCOPES);
        
        if ($auth_type == 'register') {
          $this->client->setRedirectUri(GOOGLE_REDIRECT_URI_REGISTER);
        } else {
          $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);
        }
      }
    }

    public function getAuthUrl() {
      return filter_var($this->client->createAuthUrl(), FILTER_SANITIZE_URL);
    }

    public function checkRedirectCode($code) {
      if (!empty($code)) {
        $access_token = $this->client->fetchAccessTokenWithAuthCode($code)['access_token'];

        return $access_token;
      }

      return false;
    }

    public function logout($token) {
      $this->client->revokeToken($token);
    }

    public function getPayload() {
      return $this->client->verifyIdToken();
    }
  }
?>
