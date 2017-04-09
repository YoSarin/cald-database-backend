<?php
namespace App;

use App\Exception\Http\Http500;
use App\Exception\WrongParam;
use App\Exception\Database\TreatAsCreated;

abstract class Model
{
    protected static $table = null;
    protected static $fields = [];
    protected $data = [];

    protected $enrichCondtions = [];

    protected static $cache = [];

    public static function exists($select = null, $joins = [])
    {
        $select = static::enrichSelect($select);
        if (!empty($joins)) {
            return Context::getContainer()->db->has(static::table(), $joins, $select);
        } else {
            return Context::getContainer()->db->has(static::table(), $select);
        }
    }

    public static function count($select = null)
    {
        $select = static::enrichSelect($select);
        return Context::getContainer()->db->count(static::table(), $select);
    }

    public static function load($select = null, $limit = null, $offset = 0, $joins = [])
    {
        $out = [];
        if (count($select) == 1 && isset($select["id"])) {
            if (!is_array($select["id"])) {
                $select["id"] = [$select["id"]];
            }
            foreach ($select["id"] as $key => $id) {
                $data = static::fromCache($id);
                if ($data) {
                    $out[] = static::fromArray($data);
                    unset($select["id"][$key]);
                }
            }
            if (empty($select["id"])) {
                unset($select["id"]);
            }
        }
        $db = Context::getContainer()->db;
        $select = static::enrichSelect($select);
        if ($limit) {
            $select["LIMIT"] = [(int)$offset, (int)$limit];
        }
        if (!empty($joins)) {
            $fields = static::fields(static::table());
            foreach ($joins as $name => $relation) {
                $table = preg_replace("~^(\[[^\]]+\])?\s*([a-zA-Z0-9\-\_]+).*$~", "$2", $name);
                $fields = array_merge($fields, call_user_func(['\\App\\Model\\' . ucfirst(self::camelcaseNotation($table)), 'fields'], $table));
            }
            $rows = $db->select(static::table(), $joins, $fields, $select);
        } else {
            $rows = $db->select(static::table(), static::fields(static::table()), $select);
        }

        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2] . ': ' . $db->last_query());
        }

        foreach ($rows as $data) {
            $out[] = static::fromArray($data);
        }

        return $out;
    }

    public static function loadById($id)
    {
        $out = static::load(["id" => $id]);
        return array_pop($out);
    }

    final public static function enrichSelect($select)
    {
        if ($select == null && static::getExplicitCondtions() == null) {
            return null;
        }
        if ($select == null) {
            return static::getExplicitCondtions();
        }
        if (count($select) > 0) {
            $select = ["AND" => $select];
        }
        if (static::getExplicitCondtions() == null) {
            return $select;
        }
        return ["AND" => array_merge(static::getExplicitCondtions(), $select)];
    }

    protected static function getExplicitCondtions()
    {
        return [];
    }

    protected static function fromArray($data)
    {
        $t = static::table();
        $i = new static();
        foreach ($data as $table => $row) {
            if ($table == $t) {
                $i->data = $row;
                /*
                foreach ($row as $column => $value) {
                    var_dump($column);
                    var_dump($value);
                    $i->data[substr($column, strlen($table . '_'))] = $value;
                }*/
                static::cache($row);
            } else {
                call_user_func(['\\App\\Model\\' . ucfirst(self::camelcaseNotation($table)), 'fromArray'], [$table => $row]);
            }
        }
        return $i;
    }

    protected static function cache($row)
    {
        if (!isset($row["id"])) {
            return;
        }
        if (!isset(self::$cache[static::table()])) {
            self::$cache[static::table()] = [];
        }
        self::$cache[static::table()][$row["id"]] = $row;
    }

    protected static function fromCache($id)
    {
        if (!isset(self::$cache[static::table()][$id])) {
            return null;
        }
        return [static::table() => self::$cache[static::table()][$id]];
    }

    protected static function fields($prefix)
    {
        $out = array_map(function ($i) use ($prefix) {
            return ($prefix . '.' . $i . '(' . $prefix . '_' . $i . ')');
        }, static::$fields);
        return [$prefix => $out];
    }

    protected function __construct()
    {
    }

    public function __call($name, $args)
    {
        $field = preg_replace("/^(get|set)_/", "", static::underscoreNotation($name));
        $upperCaseField = lcfirst(preg_replace("/^(get|set)/", "", $name));

        if (strpos($name, "get") === 0 && in_array($field, static::$fields)) {
            return isset($this->data[$field]) ? $this->data[$field] : null;
        } elseif (strpos($name, "set") === 0 && in_array($field, static::$fields) && count($args) == 1) {
            if (property_exists(__CLASS__, $upperCaseField . "List")) {
                if (!array_key_exists($args[0], static::${$upperCaseField . "List"})) {
                    throw new Http500("Wrong ENUM parameter: " . $args[0]);
                }
            }
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

    public function getExtendedData(&$loaded = array())
    {
        $data = [];
        if (!array_key_exists(static::table(), $loaded)) {
            $loaded[static::table()] = [];
        }
        $loaded[static::table()][$this->getId()] = $this;
        $itemData = $this->getData();
        array_walk($itemData, function ($value, $key) use (&$data, &$loaded) {
            if (preg_match("~_id$~", $key)) {
                $newKey = substr($key, 0, -3);
                if ($value && !isset($loaded[$newKey][$value])) {
                    $model = "\\App\\Model\\" . ucfirst(static::camelcaseNotation($newKey));
                    if (class_exists($model)) {
                        $item = $model::loadById($value);
                        $data[$newKey] = $item->getExtendedData($loaded);
                        return;
                    }
                }
            }

            $data[$key] = $value;
        });
        return $data;
    }

    public function save()
    {
        $this->onSaveValidation();

        $db = Context::getContainer()->db;

        $data = $this->data;
        unset($data["id"]);

        if (!empty($this->data['id'])) {
            $db->update(
                static::table(),
                $data,
                ["id" => $this->getId()]
            );
        } else {
            $id = $db->insert(
                static::table(),
                $data
            );
            $this->setId($id);
        }
        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2] . ': ' . $db->last_query());
        }
    }

    public function delete()
    {
        if ($this->isNew()) {
            return;
        }
        $db = Context::getContainer()->db;
        $db->delete(static::table(), ['id' => $this->getId()]);

        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2] . ': ' . $db->last_query());
        }
    }

    public function updateByRequest(\App\Request $r)
    {
        foreach (static::$fields as $field) {
            $v = $r->getParam($field);
            if ($v) {
                $this->{"set" . ucfirst(static::camelcaseNotation($field))}($v);
            }
        }
    }

    protected function onSaveValidation()
    {
        return true;
    }

    final public static function table()
    {
        if (empty(static::$table)) {
            $class = explode("\\", get_called_class());
            $className = array_pop($class);
            return static::underscoreNotation($className);
        }
        return static::$table;
    }

    final protected static function getRelated()
    {
        $related = [];
        return $related;
    }

    final protected function setId($id)
    {
        $this->data["id"] = $id;
    }

    final public static function underscoreNotation($string)
    {
        return trim(preg_replace_callback("/[A-Z]+/", function ($matches) {
            return "_" . strtolower($matches[0]);
        }, $string), '_');
    }

    final public static function camelcaseNotation($string)
    {
        return lcfirst(preg_replace_callback("/_([a-z])/", function ($matches) {
            return strtoupper($matches[1]);
        }, strtolower($string)));
    }

    final public static function usedTables()
    {
        $dirname = __DIR__ . '/Model';
        $dir = opendir($dirname);
        while (false !== ($entry = readdir($dir))) {
            $filename = $dirname . '/' . $entry;
            if (is_file($filename)) {
                require_once $filename;
            }
        }
        closedir($dir);
        $tables = [];
        foreach(get_declared_classes() as $class){
            if(is_subclass_of($class, '\App\Model')) {
                $tables[] = $class::table();
            }
        }
        return $tables;
    }
}
