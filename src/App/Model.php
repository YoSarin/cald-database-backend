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

    public static function exists($select = null)
    {
        $select = static::enrichSelect($select);
        return Context::getContainer()->db->has(static::table(), $select);
    }

    public static function count($select = null)
    {
        $select = static::enrichSelect($select);
        return Context::getContainer()->db->count(static::table(), $select);
    }

    public static function load($select = null, $limit = null, $offset = 0)
    {
        $db = Context::getContainer()->db;
        $select = static::enrichSelect($select);
        $out = [];
        if ($limit) {
            $select["LIMIT"] = [(int)$offset, (int)$limit];
        }
        $rows = $db->select(static::table(), static::$fields, $select);

        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2]);
        }

        foreach ($rows as $data) {
            $out[] = static::fromArray($data);
        }

        return $out;
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
        $i = new static();
        $i->data = $data;
        return $i;
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
        array_walk($this->getData(), function ($value, $key) use (&$data, &$loaded) {
            if (preg_match("~_id$~", $key)) {
                $newKey = substr($key, 0, -3);
                if (!isset($loaded[$newKey][$value])) {
                    $model = "\\App\\Model\\" . ucfirst(static::camelcaseNotation($newKey));
                    if (class_exists($model)) {
                        $data[$newKey] = $model::load(["id" => $value])[0]->getExtendedData($loaded);
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
            throw new \App\Exception\Database($db->error()[2]);
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
            throw new \App\Exception\Database($db->error()[2]);
        }
    }

    protected function onSaveValidation()
    {
        return true;
    }

    final protected static function table()
    {
        if (empty(static::$table)) {
            $class = explode("\\", get_called_class());
            $className = array_pop($class);
            return static::underscoreNotation($className);
        }
        return static::$table;
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
}
