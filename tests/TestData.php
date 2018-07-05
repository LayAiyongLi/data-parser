<?php

namespace Lay\DataParser\Tests;

use Lay\DataParser\Data;

class TestData extends Data
{
    /**
     * @String
     */
    protected $foo = '';
    /**
     * @String
     * @Mapping bar
     */
    protected $map = '';
    /**
     * @Integer
     */
    protected $exp = 0;
    /**
     * @Format
     */
    protected $fmt = '';

    public function format($val, $key, $option = array())
    {
        switch ($key) {
            case 'fmt':
                return sprintf('format %s', $val);
        }
        return parent::format($val, $key, $option);
    }
    /**
     * @DefaultKey ["exp"]
     */
    public function expand($key, $next = array(), $option = array())
    {
        switch ($key) {
            case 'exp':
                $this->__set('exp', '10000abc');
                break;
        }
        return parent::expand($key, $next, $option);
    }
}
