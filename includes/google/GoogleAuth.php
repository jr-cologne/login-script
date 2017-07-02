<?php
  class GoogleAuth {
    protected $client;

    public function __construct(Google_Client $google_client=null) {
      $this->client = $google_client;

      if ($this->client) {
        $this->client->setAuthConfig(AUTH_CONFIG_FILE);
        $this->client->setIncludeGrantedScopes(true);
        $this->client->addScope(SCOPES);
        $this->client->setRedirectUri(REDIRECT_URI);
      }
    }

    public function getAuthUrl() {
      return filter_var($this->client->createAuthUrl(), FILTER_SANITIZE_URL);
    }

    public function checkRedirectCode($code) {
      if (!empty($code)) {
        $this->client->authenticate($code);

        $this->setToken($this->client->getAccessToken());

        return true;
      }

      return false;
    }

    public function setToken($token) {
      $_SESSION['access_token'] = $token;

      $this->client->setAccessToken($token);
    }

    public function logout($token) {
      $this->client->revokeToken($token);
    }

    public function getPayload() {
      return $this->client->verifyIdToken();
    }
  }
?>