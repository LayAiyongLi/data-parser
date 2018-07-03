<?php

namespace Lay\DataParser\Tests;

use Lay\DataParser\Data;

class TestData extends Data
{
	protected $foo = 0;
	
	public function rules()
	{
		return array_merge(parent::rules(), [
			'foo' => self::TYPE_INTEGER
		]);
	}
}
