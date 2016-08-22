<?php
namespace App;

abstract class Model
{
    protected static $table = "";
    protected static $fields = [];
    protected $data = [];

    public static function exists($select)
    {
        return Context::getContainer()->db->has(static::$table, $select);
    }

    public static function load($select)
    {
        $out = [];
        foreach (Context::getContainer()->db->select(static::$table, static::$fields, $select) as $data) {
            $out[] = static::fromArray($data);
        }

        return $out;
    }

    protected static function fromArray($data)
    {
        $i = new static();
        $i->data = $data;
        return $i;
    }

    protected function __construct()
    {
    }

    public function __call($name, $args)
    {
        $field = preg_replace("/^(get|set)_/", "", preg_replace_callback("/[A-Z]+/", function ($matches) {
            return "_" . strtolower($matches[0]);
        }, $name));
        if (strpos($name, "get") === 0 && in_array($field, static::$fields)) {
            return isset($this->data[$field]) ? $this->data[$field] : null;
        } elseif (strpos($name, "set") === 0 && in_array($field, static::$fields) && count($args) == 1) {
            $this->data[$field] = $args[0];
        }
    }

    public function isNew()
    {
        return empty($this->getId());
    }

    public function getData()
    {
        return $this->data;
    }

    public function save()
    {
        $db = Context::getContainer()->db;

        $data = $this->data;
        unset($data["id"]);

        if (!empty($this->data['id'])) {
            $db->update(
                static::$table,
                $data,
                ["id" => $this->getId()]
            );
        } else {
            $id = $db->insert(
                static::$table,
                $data
            );
            $this->setId($id);
        }

        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2]);
        }
    }

    public function delete()
    {
        if ($this->isNew()) {
            return;
        }
        $db = Context::getContainer()->db;
        $db->delete(static::$table, ['id' => $this->getId()]);

        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2]);
        }
    }

    final protected function setId($id)
    {
        $this->data["id"] = $id;
    }
}
