<?php

namespace Baum\Tests\Main\Support;

class PopulateData
{
    public static function basicTree()
    {
        $data = [
            ['id' => 1, 'name' => 'A1', 'children' => [
                ['id' => 2, 'name' => 'A2'],
                ['id' => 3, 'name' => 'A3'],
            ]],
            ['id' => 4, 'name' => 'B1', 'children' => [
                ['id' => 5, 'name' => 'B2'],
                ['id' => 6, 'name' => 'B3'],
            ]],
        ];

        return $data;
    }

    public static function deepTree()
    {
        $data = [
            ['id' => 1, 'name' => 'A1', 'children' => [
                ['id' => 2, 'name' => 'A2'],
                ['id' => 3, 'name' => 'A3'],
            ]],
            ['id' => 4, 'name' => 'B1', 'children' => [
                ['id' => 5, 'name' => 'B2', 'children' => [
                    ['id' => 7, 'name' => 'B2.1'],
                    ['id' => 8, 'name' => 'B2.2'],
                    ['id' => 9, 'name' => 'B2.3'],
                ],
                ],
                ['id' => 6, 'name' => 'B3'],
            ]],
        ];

        return $data;
    }
}
