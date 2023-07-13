<?php

namespace App;

use PDO;

class DB {

  private string $path;
  private $instance;

  public function __construct ($path) {
    $create = !file_exists($path);
    touch($path);
    $this->path = $path;
    $this->instance = new PDO('sqlite:' . $path);
    $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    if ($create) {
        $sql = file_get_contents(dirname($path) . '/schema/schema.sql');
        $this->instance->exec($sql);
    }
  }

  public function getInstance () {
    return $this->instance;
  }

}
