<?php

namespace Lay\DataParser\Tests;

use Lay\DataParser\Data;
use Lay\DataParser\DataArray;
use Lay\DataParser\Tests\TestData;
use PHPUnit\Framework\TestCase;

class Tests extends TestCase
{
    public function testData()
    {
        $row = ['foo' => 'bar'];
        $data = TestData::parse($row);
        $this->assertInstanceOf(Data::class, $data);
    }
    public function testDataArray()
    {
        $rows = [['foo' => 'bar']];
        $dataArray = TestData::parseArray($rows);
        $this->assertInstanceOf(DataArray::class, $dataArray);
    }
}
