<?php
  require_once('config.php');

  class GoogleAuth {
    protected $client;

    public function __construct(Google_Client $google_client=null) {
      $this->client = $google_client;

      if ($this->client) {
        $this->client->setClientId(CLIENT_ID);
        $this->client->setClientSecret(CLIENT_SECRET);
        $this->client->setRedirectUri(REDIRECT_URI);
        $this->client->setScopes(SCOPES);
      }
    }

    public function getAuthUrl() {
      return $this->client->createAuthUrl();
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