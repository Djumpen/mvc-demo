<?php

namespace MVCApp\Model;

use Medoo\Medoo;
use Pimple\Container;

abstract class BaseModel {

    /**
     * @var Container
     */
    private $di;

    /**
     * @var Medoo
     */
    protected $db;

    public function __construct(Container $di) {
        $this->di = $di;
        $this->db = $this->di['db'];
    }

    /**
     * @return string
     */
    public function datetime(): string {
        return date("Y-m-d H:i:s");
    }

    /**
     * @param $defaults
     * @param $data
     */
    public function setDefaults(&$data, $defaults): void {
        foreach ($defaults as $key => $val) {
            if(!isset($data[$key])){
                $data[$key] = $val;
            }
        }
    }
}