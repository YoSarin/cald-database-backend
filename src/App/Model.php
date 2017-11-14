<?php
namespace App;

use App\Exception\Http\Http500;
use App\Exception\WrongParam;
use App\Exception\Database\TreatAsCreated;

abstract class Model
{
    const MAGICAL_SEPARATOR_FOR_ALIASES = '___';
    protected static $table = null;
    protected static $fields = [];
    protected $data = [];

    protected static $queryCount = 0;

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

    public static function extendedJoins(&$joins = [], $alias = "")
    {
        foreach(static::$fields as $field) {
            if (preg_match("~_id$~", $field)) {
                $tableName = substr($field, 0, -3);
                $joinAlias = $tableName . static::MAGICAL_SEPARATOR_FOR_ALIASES . static::table();
                $joinName = "[>]" . $tableName . "(" . $joinAlias . ")";
                if (!isset($joins[$joinName])) {
                    if ($alias == "") {
                        $alias = static::table();
                    }
                    $joins[$joinName] = [$alias . "." . $field => "id"];
                    $model = "\\App\\Model\\" . ucfirst(static::camelcaseNotation($tableName));
                    if (class_exists($model)) {
                        $model::extendedJoins($joins, $joinAlias);
                    }
                }
            }
        }
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

    public static function load($select = null, $limit = null, $offset = 0, $joins = [], $skipEnrich = false)
    {
        if ($select) {
            foreach ($select as $key => $value) {
                if (strpos($key, '.') === false && !in_array($key, ['OR', 'AND'])) {
                    $select[self::table() . "." . $key] = $value;
                    unset($select[$key]);
                }
            }
        }

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
                return $out;
            }
        }
        $db = Context::getContainer()->db;
        if (!$skipEnrich) {
            $select = static::enrichSelect($select);
        }
        if ($limit) {
            $select["LIMIT"] = [(int)$offset, (int)$limit];
        }
        if (!empty($joins)) {
            $fields = static::fields(static::table());
            foreach ($joins as $name => $relation) {
                preg_match("~^(\[[^\]]+\])?\s*([a-zA-Z0-9\-\_]+)(\((.*)\))?$~", $name, $matches);
                if (!isset($matches[2])) {
                    continue;
                }
                $table = $matches[2];
                if (isset($matches[4])) {
                    $prefix = $matches[4];
                } else {
                    $prefix = $table;
                }
                $newFields = call_user_func(['\\App\\Model\\' . ucfirst(self::camelcaseNotation($table)), 'fields'], $prefix);
                $fields = array_merge($fields, $newFields);
            }
            self::$queryCount++;
            $rows = $db->select(static::table(), $joins, $fields, $select);
            // \App\Context::getContainer()->logger->info("DB calls: " . self::$queryCount . " complex (" . static::table() . ":" . count($rows) . ")");
        } else {
            self::$queryCount++;
            $rows = $db->select(static::table(), static::fields(static::table()), $select);
            // \App\Context::getContainer()->logger->info("DB calls: " . self::$queryCount . " simple (" . static::table() . ")");
            // \App\Context::getContainer()->logger->info($db->last_query());
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
        return $out[0];
    }

    final public static function enrichSelect($select)
    {
        if ($select == null && static::getExplicitCondtions() == null) {
            return null;
        }
        if ($select == null) {
            return static::getExplicitCondtions();
        }
        if (static::getExplicitCondtions() == null) {
            if (count($select) > 0) {
                $select = ["AND" => $select];
            }
            return $select;
        }
        $out = ["AND" => array_merge(static::getExplicitCondtions(), $select)];
        return $out;
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
                static::cache($row);
            } else {
                $table = explode(self::MAGICAL_SEPARATOR_FOR_ALIASES, $table)[0];
                $class = '\\App\\Model\\' . ucfirst(self::camelcaseNotation($table));
                if (class_exists($class)) {
                    call_user_func([$class, 'fromArray'], [$table => $row]);
                }
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
