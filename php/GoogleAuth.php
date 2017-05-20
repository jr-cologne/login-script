<?php
  class GoogleAuth {
    protected $client;

    public function __construct(Google_Client $google_client=null) {
      $this->client = $google_client;

      if ($this->client) {
        $this->client->setClientId('374519720876-f0vvtnsi6prh6oepehtj9e2vgif8u2fd.apps.googleusercontent.com');
        $this->client->setClientSecret('mlWv7EqrB2BLPVmfV7_JHIfS');
        $this->client->setRedirectUri('http://localhost/GitHub/login-script/login.php');
        $this->client->setScopes('email');
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