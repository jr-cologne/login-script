<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Controllers\Exception\ControllerException,
  Config\Config,
  User\BaseUser,
  User\User,
  User\GoogleUser,
  User\TwitterUser,
  Input\Input
};

abstract class Controller {

  protected $dependencies;

  protected $do_not_escape = [];

  protected $page_data = null;

  protected $user = null;

  public function __construct($dependencies) {
    $this->dependencies = $dependencies;
    $this->do_not_escape = Config::get('input/do_not_escape', false);
  }

  abstract public function run();

  public function setPageData(array $data) : bool {
    $this->page_data = $data;

    if (!isset($this->page_data)) {
      throw new ControllerException('Failed to set page data');
    }

    return true;
  }

  public function getPageData() : array {
    if (!isset($this->page_data)) {
      throw new ControllerException('Failed to retrieve page data');
    }

    return $this->page_data;
  }

  public function setPageItems(array $items) : bool {
    foreach ($items as $key => $value) {
      $this->page_data[$key] = $value;

      if (!isset($this->page_data[$key])) {
        throw new ControllerException('Failed to set page item');
      }
    }

    return true;
  }

  public function getPageItems(array $keys) : array {
    $items = [];

    foreach ($keys as $key) {
      if (!isset($this->page_data[$key])) {
        throw new ControllerException('Failed to retrieve page item');
      }

      $items[$key] = $this->page_data[$key];
    }

    return $items;
  }

  public function setPageItem(string $key, $value) : bool {
    $this->page_data[$key] = $value;

    if (!isset($this->page_data[$key])) {
      throw new ControllerException('Failed to set page item');
    }

    return true;
  }

  public function getPageItem(string $key) {
    if (!isset($this->page_data[$key])) {
      throw new ControllerException('Failed to retrieve page item');
    }

    return $this->page_data[$key];
  }

  public function guest() : bool {
    return !$this->getUserInstance()->isLoggedIn();
  }

  public function getResponseData(array $response_data = null) : array {
    if (empty($response_data)) {
      return [];
    }

    return Input::escapeData($response_data, $this->do_not_escape);
  }

  protected function getUserInstance(string $type = 'default') : BaseUser {
    if ($this->user && $this->rightUserType($this->user, $type)) {
      return $this->user;
    }

    switch ($type) {
      case 'google':
        $this->user = new GoogleUser($this->dependencies->db);
        break;

      case 'twitter':
        $this->user = new TwitterUser($this->dependencies->db);
        break;

      case 'default':
        $this->user = new User($this->dependencies->db);
        break;
    }

    return $this->user;
  }

  protected function rightUserType(User $user, string $type) {
    switch ($type) {
      case 'google':
        if ($this->user instanceof GoogleUser) {
          return true;
        }
        break;

      case 'twitter':
        if ($this->user instanceof TwitterUser) {
          return true;
        }
        break;

      default:
        if ($this->user instanceof User) {
          return true;
        }
        break;
    }
  }

  protected function get() : bool {
    return !empty($_GET);
  }

  protected function post() : bool {
    return !empty($_POST);
  }

  protected function getRequestData(string $type = 'post', bool $escape = true) : array {
    if ( !empty($_POST) && $type == 'post' ) {
      if (!$escape) {
        $do_not_escape = array_merge($this->do_not_escape, array_keys($_POST));
      } else {
        $do_not_escape = $this->do_not_escape;
      }

      return Input::escapeData($_POST, $do_not_escape);
    } else if ( !empty($_GET) && $type == 'get' ) {
      if (!$escape) {
        $do_not_escape = array_merge($this->do_not_escape, array_keys($_GET));
      } else {
        $do_not_escape = $this->do_not_escape;
      }

      return Input::escapeData($_GET, $do_not_escape);
    }

    return [];
  }

  protected function getValidationData(array $data) : array {
    array_splice($data, -2);

    return $data;
  }

}
