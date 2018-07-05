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
        $row = new \stdClass;
        $row->foo = 'bar';
        $row->bar = 'com';
        $row->fmt = 'org';
        $data = TestData::parse($row);
        print_r($data);
        $this->assertInstanceOf(Data::class, $data);
    }
    public function testDataArray()
    {
        $rows = [['foo' => 'bar', 'bar' => 'com', 'fmt' => 'org']];
        $dataArray = TestData::parseArray($rows);
        print_r($dataArray);
        $this->assertInstanceOf(DataArray::class, $dataArray);
    }
}
