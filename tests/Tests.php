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
        $row = ['foo' => 'bar', 'map' => 'ksn'];
        $data = TestData::parse($row);
        var_dump($data);
        $this->assertInstanceOf(Data::class, $data);
    }
    public function testDataArray()
    {
        $rows = [['foo' => 'bar', 'map' => 'ksn']];
        $dataArray = TestData::parseArray($rows);
        var_dump($dataArray);
        $this->assertInstanceOf(DataArray::class, $dataArray);
    }
}
