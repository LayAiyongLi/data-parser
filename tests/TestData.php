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
	 * @Mapping "map"
	 */
	protected $bar = '';
	/**
	 * @Integer
	 */
	protected $exp = 0;
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
