<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;

class Team extends \App\Model
{
    protected static $fields = ["id", "name", "founded_at", "city", "www", "email"];

    public static function create($name, $city = null, $www = null, $email = null, $foundedAt = null)
    {
        $i = new self();
        $i->setName($name);
        $i->setCity($city);
        $i->setWww($www);
        $i->setEmail($email);
        $i->setFoundedAt($foundedAt);

        return $i;
    }

    protected function onSaveValidation()
    {
        if ($this->isNew() && self::exists([
            "name" => $this->getName()
        ])) {
            throw new Duplicate("Privilege already exists");
        }
    }
}
