<?php

namespace Lay\DataParser;

class DataArray extends Data
{
    private $_rows = [];

    /**
     * 构造方法.
     */
    public function __construct(array $rows = [])
    {
        $this->_rows = $rows;
    }

    /**
     * 输出StdData数组.
     */
    public function output()
    {
        return $this->_rows;
    }

    /**
     * PHP 5.4
     * json serialize function.
     *
     * @return stdClass
     */
    public function jsonSerialize()
    {
        return $this->_rows;
    }

    /**
     * 返回序列化后的字符串.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->_rows);
    }

    public function expand($key, $next = array(), $option = array())
    {
        foreach ($this->_rows as &$row) {
            if ($row instanceof Data) {
                $row->expand($key, $next, $option);
            }
        }

        return $this;
    }
}