<?php

namespace Lay\DataParser;

use DocBlockReader\Reader;
use stdClass;

/**
 * @Ignore 忽略
 * @String 字符型
 * @Number 数值型
 * @Integer 数值型
 * @Float 浮点型
 * @Double 双精度浮点型
 * @Array 数组型
 * @StdClass 对象型
 * @Boolean 布尔型
 * @Datetime 日期时间型
 * @Date 日期型
 * @Time 时间型
 * @DateFormat 自定义时间型
 * @Json Json类型
 * @Format 自定义类型
 *
 * @Mapping 输入别名
 */
abstract class Data implements \ArrayAccess, \Iterator, \JsonSerializable
{
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
        // $mapping = $object->mapping();
        if (is_object($obj)) {
            foreach ($object->properties() as $pro => $def) {
                // property and property mapping
                $reader = new Reader(get_called_class(), $pro, 'property');
                $params = $reader->getParameters();
                $map = false;
                if(!empty($params)) {
                    foreach ($params as $param => $option) {
                        if(strtolower($param) == 'mapping') {
                            $map = is_string($option) ? $option : $map;
                        }
                    }
                }
                // $map = array_key_exists($pro, $mapping) ? $mapping[$pro] : false;
                $prop = !($map && isset($obj->$map)) ? $pro : $map;
                $object->__set($pro, isset($obj->$prop) && !is_null($obj->$prop) ? $obj->$prop : $def);
            }
        } elseif (is_array($obj) && !empty($obj)) {
            foreach ($object->properties() as $pro => $def) {
                // property and property mapping
                $reader = new Reader(get_called_class(), $pro, 'property');
                $params = $reader->getParameters();
                $map = false;
                if(!empty($params)) {
                    foreach ($params as $param => $option) {
                        if(strtolower($param) == 'mapping') {
                            $map = is_string($option) ? $option : $map;
                        }
                    }
                }
                // $map = array_key_exists($pro, $mapping) ? $mapping[$pro] : false;
                $prop = !($map && isset($obj[$map])) ? $pro : $map;
                $object->__set($pro, isset($obj[$prop]) && !is_null($obj[$prop]) ? $obj[$prop] : $def);
            }
        }
        // 扩展一些属性
        $reader = new Reader(get_called_class(), 'expand');
        $params = $reader->getParameters();
        $defs = [];
        if(!empty($params)) {
            foreach ($params as $param => $option) {
                if (strtolower($param) == 'defaultkey') {
                    $defs = is_array($option) ? $option : $defs;
                }
            }
        }
        // $defs = $object->expandKeys();
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
        $reader = new Reader(get_called_class(), $name, 'property');
        $params = $reader->getParameters();
        if(!empty($params)) {
            foreach ($params as $param => $option) {
                switch (strtolower($param)) {
                    case 'string':
                        $this->$name = trim(strval($value));
                        break;
                    case 'integer':
                        $this->$name = intval($value);
                        break;
                    case 'number':
                        $this->$name = 0 + $value;
                        break;
                    case 'boolean':
                        $this->$name = $value ? true : false;
                        break;
                    case 'datetime':
                        if ('0000-00-00' == $value || '0000-00-00 00:00:00' == $value || '00:00:00' == $value || empty($value)) {
                            $this->$name = '';
                        } else {
                            $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date('Y-m-d H:i:s', strtotime($value)) : date('Y-m-d H:i:s', intval($value));
                        }
                        break;
                    case 'date':
                        if ('0000-00-00' == $value || '0000-00-00 00:00:00' == $value || '00:00:00' == $value || empty($value)) {
                            $this->$name = '';
                        } else {
                            $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date('Y-m-d', strtotime($value)) : date('Y-m-d', intval($value));
                        }
                        break;
                    case 'time':
                        if ('0000-00-00' == $value || '0000-00-00 00:00:00' == $value || '00:00:00' == $value || empty($value)) {
                            $this->$name = '';
                        } else {
                            $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date('H:i:s', strtotime($value)) : date('H:i:s', intval($value));
                        }
                        break;
                    case 'dateformat':
                        if ('0000-00-00' == $value || '0000-00-00 00:00:00' == $value || '00:00:00' == $value || empty($value)) {
                            $this->$name = '';
                        } else {
                            $format = is_string($option) ? $option : 'Y-m-d H:i:s';
                            $this->$name = !is_numeric($value) ? !is_string($value) ? $this->$name : date($format, strtotime($value)) : date($format, intval($value));
                        }
                        break;
                    case 'float':
                        $round = is_integer($option) ? $option : true;
                        $this->$name = $round === true ? floatval($value) : round(floatval($value), $round);
                        break;
                    case 'double':
                        $round = is_integer($option) ? $option : true;
                        $this->$name = $round === true ? doubleval($value) : round(doubleval($value), $round);
                        break;
                    case 'array':
                        $this->$name = !is_array($value) ? $this->$name : $value;
                        break;
                    case 'stdclass':
                    case 'object':
                        $this->$name = empty($value) || !is_object($value) ? new stdClass() : $value;
                        break;
                    case 'json':
                        if (is_string($value)) {
                            $value = json_decode($value);
                        } elseif (empty($value)) {
                            $value = new stdClass();
                        } elseif (is_object($value) || is_array($value)) {
                            $value = json_decode(json_encode($value));
                        } else {
                            $value = new stdClass();
                        }
                        break;
                    case 'format':
                        $options = $option === true ? [] : $option;
                        $this->$name = $this->format($value, $name, $options);
                        break;
                    
                    default:
                        break;
                }
            }
        } else {
            $this->$name = $value;
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