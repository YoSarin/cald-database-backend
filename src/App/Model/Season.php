<?php
namespace App\Model;

class Season extends \App\Model
{
    protected static $fields = ["id", "name", "start"];

    public static function create($name, $start) {
        $s = new self();
        $s->setName($name);
        $s->setStart($start);

        return $s;
    }
}
