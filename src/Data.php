<?php

namespace Lay\DataParser;

class Data implements \ArrayAccess, \Iterator, \JsonSerializable
{
    /**
     * 忽略类型的属性值
     *
     * @var int
     */
    const TYPE_IGNORE = 0;
    /**
     * 字符串类型的属性值
     *
     * @var int
     */
    const TYPE_STRING = 1;
    const TYPE_STR = 1;
    /**
     * 数值类型的属性值
     *
     * @var int
     */
    const TYPE_NUMBER = 2;
    /**
     * 整数类型的属性值
     *
     * @var int
     */
    const TYPE_INTEGER = 3;
    const TYPE_INT = 3;
    /**
     * 布尔类型的属性值
     *
     * @var int
     */
    const TYPE_BOOLEAN = 4;
    const TYPE_BOOL = 4;
    /**
     * 日期时间类型的属性值
     *
     * @var int
     */
    const TYPE_DATETIME = 5;
    /**
     * 日期类型的属性值
     *
     * @var int
     */
    const TYPE_DATE = 6;
    /**
     * 时间类型的属性值
     *
     * @var int
     */
    const TYPE_TIME = 7;
    /**
     * 浮点数类型的属性值
     *
     * @var int
     */
    const TYPE_FLOAT = 8;
    /**
     * double类型的属性值
     *
     * @var int
     */
    const TYPE_DOUBLE = 9;
    /**
     * 数组类型的属性值
     *
     * @var int
     */
    const TYPE_ARRAY = 10;
    /**
     * 数组类型的属性值
     *
     * @var int
     */
    const TYPE_PURE_ARRAY = 11;
    /**
     * 特定格式类型的属性值
     *
     * @var int
     */
    const TYPE_DATEFORMAT = 12;
    /**
     * 指定值范围的属性值
     *
     * @var int
     */
    const TYPE_ENUM = 13;
    /**
     * 其他类型的属性值
     *
     * @var int
     */
    const TYPE_FORMAT = 14;
    /**
     * stdClass.
     *
     * @var int
     */
    const TYPE_STDCLASS = 15;
    /**
     * json object array.
     *
     * @var int
     */
    const TYPE_JSON_OBJECT = 16;
    /**
     * json array.
     *
     * @var int
     */
    const TYPE_JSON_ARRAY = 17;
    /**
     * object.
     *
     * @var int
     */
    const TYPE_OBJECT = 18;
    /**
     * all components' properties.
     */
    protected static $properties = array();

    /**
     * 是关联数组还是普通数组.
     *
     * @param array $arr
     *
     * @return boolean
     */
    public static function isAssocArray($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        $keys = array_keys($arr);

        return array_keys($keys) !== $keys;
    }
    /**
     * 转变为纯粹的数组.
     *
     * @param array $arr
     *
     * @return array
     */
    public static function toPureArray($arr)
    {
        if (is_array($arr)) {
            return array_values($arr);
        } elseif (is_object($arr)) {
            if ($arr instanceof Data || method_exists($arr, 'toArray')) {
                return self::toPureArray($arr->toArray());
            } else {
                return self::toPureArray(get_object_vars($arr));
            }
        } elseif (!is_resource($arr)) {
            return (array) $arr;
        } else {
            return $arr;
        }
    }
    /**
     * 实例化
     * @return Data
     */
    public static function instance()
    {
        $clazz = get_called_class();

        return new $clazz();
    }
    /**
     * 根据mapping()方法中的映射属性，将查询结果转换为Data类型输出结果。
     * 允许扩展某些属性。
     *
     * @param object|array $obj
     *
     * @return Data
     */
    public static function parse($obj, $keys = array())
    {
        $object = self::instance();
        $mapping = $object->mapping();
        if (is_object($obj)) {
            foreach ($object->properties() as $pro => $def) {
                // property and property mapping
                $map = array_key_exists($pro, $mapping) ? $mapping[$pro] : false;
                $prop = !($map && isset($obj->$map)) ? $pro : $map;
                $object->__set($pro, isset($obj->$prop) && !is_null($obj->$prop) ? $obj->$prop : $def);
            }
        } elseif (is_array($obj) && !empty($obj)) {
            foreach ($object->properties() as $pro => $def) {
                // property and property mapping
                $map = array_key_exists($pro, $mapping) ? $mapping[$pro] : false;
                $prop = !($map && isset($obj[$map])) ? $pro : $map;
                $object->__set($pro, isset($obj[$prop]) && !is_null($obj[$prop]) ? $obj[$prop] : $def);
            }
        }
        // 扩展一些属性
        $defs = $object->expandKeys();
        $keys = array_merge($defs, $keys);
        if (!empty($keys)) {
            foreach ($keys as $key => $next) {
                if (is_numeric($key)) {
                    $key = $next;
                    $next = [];
                }
                $val = $object->expand($key, $next);
            }
        }

        return $object;
    }

    /**
     * 根据mapping()方法中的映射属性，将查询结果数组转换为Data类型输出结果数组。
     * 允许扩展某些属性。
     *
     * @param array<object|array> $arr
     * @param array               $keys
     *
     * @return DataArray
     */
    public static function parseArray($arr, $keys = array())
    {
        $objects = array();
        $class = get_called_class();
        foreach ($arr as $val) {
            $objects[] = $class::parse($val, $keys);
        }

        return new DataArray($objects);
    }

    /**
     * 构造方法.
     */
    public function __construct()
    {
        foreach ($this->properties() as $pro => $def) {
            $this->$pro = $def;
        }
    }

    /**
     * 返回对象所有属性名的数组.
     *
     * @return array
     */
    protected function properties()
    {
        $class = get_class($this);
        if (!isset(self::$properties[$class])) {
            self::$properties[$class] = get_object_vars($this);
        }

        return self::$properties[$class];
    }

    protected function expandKeys()
    {
        return [];
    }

    /**
     * 扩展属性.
     *
     * @param string $key
     * @param array  $next 子项
     */
    public function expand($key, $next = array(), $option = array())
    {
        return $this;
    }

    /**
     * 返回对象属性映射关系.
     *
     * @return array
     */
    public function mapping()
    {
        return array();
    }

    /**
     * 返回对象所有属性值规则.
     *
     * @return array
     */
    public function rules()
    {
        return array();
    }

    /**
     * 返回规则格式化后的值
     *
     * @return array
     */
    public function format($val, $key, $option = array())
    {
        return $val;
    }

    /**
     * 清空对象所有属性值
     *
     * @return Beanizable
     */
    public function restore()
    {
        // 恢复默认值
        foreach ($this->properties() as $name => $value) {
            $this->$name = $value;
        }

        return $this;
    }

    /**
     * 输出.
     * @return \stdClass
     */
    public function output()
    {
        return $this->toStandard();
    }

    /**
     * 返回对象属性名对属性值的数组.
     *
     * @return array
     */
    public function toArray()
    {
        $ret = array();
        foreach ($this->properties() as $name => $def) {
            $ret[$name] = $this->_toArray($this->$name);
        }

        return $ret;
    }

    /**
     * 迭代返回对象属性名对属性值的数组.
     *
     * @param mixed $val
     *
     * @return mixed
     */
    protected function _toArray($val)
    {
        if (is_array($val)) {
            $var = array();
            foreach ($val as $k => $v) {
                $var[$k] = $this->_toArray($v);
            }

            return $var;
        } elseif (is_object($val) && ($val instanceof Data || method_exists($val, 'toArray'))) {
            return $val->toArray();
        } elseif (is_object($val)) {
            return $this->_toArray(get_object_vars($val));
        } else {
            return $val;
        }
    }

    /**
     * 返回对象转换为stdClass后的对象
     *
     * @return stdClass
     */
    public function toStandard()
    {
        $ret = new stdClass();
        foreach ($this->properties() as $name => $value) {
            $ret->$name = $this->_toStandard($this[$name]);
        }

        return $ret;
    }

    /**
     * 迭代返回对象转换为stdClass后的对象
     *
     * @param mixed $var
     *
     * @return mixed
     */
    protected function _toStandard($val)
    {
        if (is_array($val) && self::isAssocArray($val)) {
            $var = new stdClass();
            foreach ($val as $k => $v) {
                $var->$k = $this->_toStandard($v);
            }

            return $var;
        } elseif (is_array($val)) {
            $var = array();
            foreach ($val as $k => $v) {
                $var[$k] = $this->_toStandard($v);
            }

            return $var;
        } elseif ($val instanceof Data) {
            return $val->toStandard();
        } elseif (is_object($val)) {
            $var = new stdClass();
            foreach (get_object_vars($val) as $k => $v) {
                $var->$k = $this->_toStandard($v);
            }

            return $var;
        //return $this->_toStandard(get_object_vars($val));
        } else {
            return $val;
        }
    }

    /**
     * PHP 5.4
     * json serialize function.
     *
     * @return stdClass
     */
    public function jsonSerialize()
    {
        return $this->toStandard();
    }

    /**
     * 自动执行方法,即执行get和set方法.
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ('get' === strtolower(substr($method, 0, 3))) {
            // $name = strtolower(substr($method,3,1)).substr($method,4);
            $name = substr($method, 3);
            $name = strtolower(substr($name, 0, 1)).substr($name, 1);

            return $this->$name;
        } elseif ('set' === strtolower(substr($method, 0, 3))) {
            // $name = strtolower(substr($method,3,1)).substr($method,4);
            $name = substr($method, 3);
            $name = strtolower(substr($name, 0, 1)).substr($name, 1);
            $this->__set($name, $arguments[0]);
        } else {
            throw new \Exception("Undefined method $method in class ".get_called_class());
        }
    }

    /**
     * 设置对象属性值的魔术方法.
     *
     * @param string $name  属性名
     * @param mixed  $value 属性值
     */
    final public function __set($name, $value)
    {
        if ($this->__isset($name)) {
            $rules = $this->rules();
            if (!empty($rules) && array_key_exists($name, $rules)) {
                switch ($rules[$name]) {
                    case self::TYPE_IGNORE:
                        $this->$name = $value;
                        break;
                    case self::TYPE_STRING:
                        $this->$name = trim(strval($value));
                        break;
                    case self::TYPE_NUMBER:
                        $this->$name = 0 + $value;
                        break;
                    case self::TYPE_INTEGER:
                        $this->$name = intval($value);
                        break;
                    case self::TYPE_BOOLEAN:
                        $this->$name = $value ? true : false;
                        break;
                    case self::TYPE_DATETIME:
                        if ('0000-00-00 00:00:00' == $value || empty($value)) {
                            $this->$name = '';
                        } else {
                            $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date('Y-m-d H:i:s', strtotime($value)) : date('Y-m-d H:i:s', intval($value));
                        }
                        break;
                    case self::TYPE_DATE:
                        if ('0000-00-00' == $value || '0000-00-00 00:00:00' == $value || empty($value)) {
                            $this->$name = '';
                        } else {
                            $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date('Y-m-d', strtotime($value)) : date('Y-m-d', intval($value));
                        }
                        break;
                    case self::TYPE_TIME:
                        $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date('H:i:s', strtotime($value)) : date('H:i:s', intval($value));
                        break;
                    case self::TYPE_FLOAT:
                        $this->$name = floatval($value);
                        break;
                    case self::TYPE_DOUBLE:
                        $this->$name = doubleval($value);
                        break;
                    case self::TYPE_ARRAY:
                        $this->$name = !is_array($value) ? $this->$name : $value;
                        break;
                    case self::TYPE_PURE_ARRAY:
                        $this->$name = !is_array($value) ? $this->$name : self::toPureArray($value);
                        break;
                    case self::TYPE_STDCLASS:
                        $this->$name = empty($value) || !is_object($value) ? new stdClass() : $value;
                        break;
                    case self::TYPE_JSON_OBJECT:
                        if (is_string($value)) {
                            $value = json_decode($value, true);
                        }
                        if (empty($value)) {
                            $value = new stdClass();
                        } elseif (is_object($value) && method_exists($value, 'toArray')) {
                            $value = $value->toArray();
                        } elseif (is_object($value)) {
                            $value = get_object_vars($value);
                        } else {
                            $value = new stdClass();
                        }
                        $this->$name = self::isAssocArray($value) ? $value : new stdClass();
                        break;
                    case self::TYPE_JSON_ARRAY:
                        if (is_string($value)) {
                            $value = json_decode($value, true);
                        }
                        if (empty($value)) {
                            $value = array();
                        } elseif (is_object($value) && method_exists($value, 'toArray')) {
                            $value = $value->toArray();
                        } elseif (is_object($value)) {
                            $value = get_object_vars($value);
                        } else {
                            $value = array();
                        }
                        $this->$name = self::isAssocArray($value) ? array_values($value) : $value;
                        break;
                    case self::TYPE_OBJECT:
                        $this->$name = is_null($value) || !is_object($value) ? null : $value;
                        break;
                    case self::TYPE_FORMAT:
                        $this->$name = $this->format($value, $name);
                        break;
                    default:
                        if (is_array($rules[$name]) && $pure = self::toPureArray($rules[$name])) {
                            if (count($pure) > 1 && self::TYPE_DATEFORMAT == $pure[0]) {
                                $this->$name = !is_numeric($value) ? !is_string($value) ?: date($pure[1], strtotime($value)) : date($pure[1], intval($value));
                            } elseif (count($pure) > 1 && self::TYPE_ENUM == $pure[0]) {
                                $this->$name = !in_array($value, (array) $pure[1]) ?: $value;
                            } elseif (count($pure) > 1 && self::TYPE_FORMAT == $pure[0]) {
                                $this->$name = $this->format($value, $name, (array) $pure[1]);
                            } elseif (count($pure) > 1 && self::TYPE_FLOAT == $pure[0]) {
                                $this->$name = round(floatval($value), $pure[1]);
                            } elseif (count($pure) > 1 && self::TYPE_DOUBLE == $pure[0]) {
                                $this->$name = round(doubleval($value), $pure[1]);
                            }
                        }
                        break;
                }
            } else {
                $this->$name = $value;
            }
        } else {
            throw new \Exception("Undefined property $name in class ".get_called_class());
        }
    }

    /**
     * 设置对象属性值的魔术方法.
     *
     * @param string $name 属性名
     */
    final public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            throw new \Exception("Undefined property $name in class ".get_called_class());
        }
    }

    /**
     * 检测属性是否设置.
     *
     * @param string $name
     *                     属性名
     *
     * @return bool
     */
    final public function __isset($name)
    {
        $properties = $this->properties();
        if (self::isAssocArray($properties)) {
            return array_key_exists($name, $properties);
        } else {
            return array_key_exists($name, array_flip($properties));
        }
    }

    /**
     * 无法将某个属性去除.
     *
     * @param string $name
     *                     属性名
     */
    final public function __unset($name)
    {
        return false;
    }

    /**
     * 返回序列化后的字符串.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * @see Iterator::current()
     */
    public function current()
    {
        return current($this);
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        return next($this);
    }

    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return key($this);
    }

    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        return null !== key($this);
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        return reset($this);
    }

    /**
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($index)
    {
        return isset($this->$index);
    }

    /**
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($index)
    {
        return $this->$index;
    }

    /**
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($index, $value)
    {
        $this->$index = $value;
    }

    /**
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($index)
    {
        return false;
    }
}