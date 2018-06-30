<?php

namespace LoginScript\Core;

use LoginScript\{
  Core\Exception\AppException,
  Config\Config, Session\Session,
  Controllers\Controller
};

class App {

  protected $dependencies = [];

  public function __construct(array $dependencies = [], array $config = []) {
    if (!$dependencies) {
      throw new AppException('No dependencies specified');
    }

    if (!$config) {
      throw new AppException('No config specified');
    }

    Config::set($config);

    $this->dependencies = $this->initDependencies($dependencies);
  }

  public function boot() {
    if (!$this->dependencies) {
      throw new AppException('No dependencies found');
    }

    if (!Session::init()) {
      throw new AppException('Session initialization failed');
    }

    if ( !$this->dependencies->db->connect(Config::get('database/type') . ':dbname=' . Config::get('database/name') . ';host=' . Config::get('database/host') . ';charset=utf8', Config::get('database/user'), Config::get('database/password')) ) {
      throw new AppException('Database connection failed');
    }
  }

  public function controller(string $controller) : Controller {
    try {
      if ($controller[0] == ':') {
        $controller = 'LoginScript\Controllers\\' . substr($controller, 1);
      } else {
        $controller = 'LoginScript\Controllers\\' . ucfirst(strtolower($controller));
      }

      $controller = new $controller($this->dependencies);
      $controller->run();

      return $controller;
    } catch (Exception $e) {
      throw new AppException('Invalid/Unknown controller');
    }
  }

  protected function initDependencies(array $dependencies) {
    foreach ($dependencies as $key => $dependency) {
      if ( !is_a($dependency, Config::get("dependencies/{$key}")) ) {
        throw new AppException('Invalid/Unknown dependency');
      }
    }

    return (object) $dependencies;
  }

}
