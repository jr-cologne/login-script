<?php
  class DB {  
    public $pdo = null;
    protected static $env = 'production';
    protected static $error_types = [
      0 => 'success',
      1 => 'Connection to database failed',
      2 => 'Selecting/Getting data from database failed',
      3 => 'Inserting data into database failed',
      4 => 'Deleting data from database failed',
      5 => 'Updating data in database failed',
    ];
    protected $error = [ 'code' => 0, 'msg' => null ];

    // initialize error handler / define configs for error handling
    public static function initErrorHandler(array $error_types=[], string $env='production') {
      self::$env = $env;

      if ($error_types == []) {
        self::$error_types = self::$error_types;
      } else {
        self::$error_types = $error_types;
      }
    }

    // check for error
    public function error() {
      $error = $this->error;

      return ( !empty($error) && $error['code'] != 0 && $error['msg'] != null ) ? true : false;
    }

    // get error
    public function getError() {
      $env = self::$env;
      $error_types = self::$error_types;
      $error = $this->error;

      if (empty($error)) {
        return null;
      }

      switch ($env) {
        case 'production':
          unset($error['pdo_exception']);
          return $error;
          break;

        case 'development':
        case 'dev':
          return $error;
          break;
        
        default:
          return false;
          break;
      }
    }

    // connect to database
    public function __construct(string $dbname, string $user, string $password, string $db_type='mysql', string $host='localhost', int $pdo_err_mode=PDO::ERRMODE_EXCEPTION) {
      $error_types = self::$error_types;

      try {
        $pdo = new PDO($db_type . ':host=' . $host . ';dbname=' . $dbname, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, $pdo_err_mode);
      } catch (PDOException $e) {
        $this->error = [ 'code' => 1, 'msg' => $error_types[1], 'pdo_exception' => $e ];
        $pdo = null;
      }

      $this->error = $this->getError();
      $this->pdo = $pdo;
    }

    // check if connection to database is established
    public function connected() {
      return (!empty($this->pdo)) ? true : false; 
    }

    // select/get data from database
    public function select(string $sql, array $where=null, int $fetch_mode=PDO::FETCH_ASSOC) {
      $error_types = self::$error_types;
      $pdo = $this->pdo;

      try {
        $statement = $pdo->prepare($sql);

        if (!empty($where)) {
          $execution = $statement->execute($where);
        } else {
          $execution = $statement->execute();
        }

        $results = $statement->fetchAll($fetch_mode);
      } catch (PDOException $e) {
        $this->error = [ 'code' => 2, 'msg' => $error_types[2], 'pdo_exception' => $e ];
        return $this->getError();
      }

      if ($execution === true && !empty($results)) {
        return $results;
      } else if ($execution === true && empty($results)) {
        return null;
      } else {
        return false;
      }
    }

    // insert data into database
    public function insert(string $sql, array $values) {
      $error_types = self::$error_types;
      $pdo = $this->pdo;

      try {
        $statement = $pdo->prepare($sql);
        $execution = $statement->execute($values);
      } catch (PDOException $e) {
        $this->error = [ 'code' => 3, 'msg' => $error_types[3], 'pdo_exception' => $e ];
        return $this->getError();
      }

      if ($execution) {
        return true;
      } else {
        return false;
      }
    }

    // delete data/rows from database
    public function delete(string $sql, array $where=null) {
      $error_types = self::$error_types;
      $pdo = $this->pdo;

      try {
        $statement = $pdo->prepare($sql);

        if (!empty($where)) {
          $execution = $statement->execute($where);
        } else {
          $execution = $statement->execute();
        }
      } catch (PDOException $e) {
        $this->error = [ 'code' => 4, 'msg' => $error_types[4], 'pdo_exception' => $e ];
        return $this->getError();
      }

      if ($execution) {
        return true;
      } else {
        return false;
      }
    }

    // update data/rows in database
    public function update(string $sql, array $values) {
      $error_types = self::$error_types;
      $pdo = $this->pdo;

      try {
        $statement = $pdo->prepare($sql);

        $execution = $statement->execute($values);
      } catch (PDOException $e) {
        $this->error = [ 'code' => 5, 'msg' => $error_types[5], 'pdo_exception' => $e ];
        return $this->getError();
      }

      if ($execution) {
        return true;
      } else {
        return false;
      }
    }
  }
?>
