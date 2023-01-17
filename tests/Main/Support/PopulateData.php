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

    public static function multiScoped()
    {
        $data = [
            ['id' => 1, 'company_id' => 1, 'language' => 'en', 'name' => 'Root 1', 'children' => [
                ['id' => 2, 'company_id' => 1, 'language' => 'en', 'name' => 'Child 1'],
                ['id' => 3, 'company_id' => 1, 'language' => 'en', 'name' => 'Child 2', 'children' => [
                    ['id' => 4, 'company_id' => 1, 'language' => 'en', 'name' => 'Child 2.1'],
                ]],
                ['id' => 5, 'company_id' => 1, 'language' => 'en', 'name' => 'Child 3'],
            ]],
            ['id' => 6, 'company_id' => 2, 'language' => 'en', 'name' => 'Root 2', 'children' => [
                ['id' => 7, 'company_id' => 2, 'language' => 'en', 'name' => 'Child 4'],
                ['id' => 8, 'company_id' => 2, 'language' => 'en', 'name' => 'Child 5', 'children' => [
                    ['id' => 9, 'company_id' => 2, 'language' => 'en', 'name' => 'Child 5.1'],
                ]],
                ['id' => 10, 'company_id' => 2, 'language' => 'en', 'name' => 'Child 6'],
            ]],
            ['id' => 11, 'company_id' => 3, 'language' => 'fr', 'name' => 'Racine 1', 'children' => [
                ['id' => 12, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 1'],
                ['id' => 13, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 2', 'children' => [
                    ['id' => 14, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 2.1'],
                ]],
                ['id' => 15, 'company_id' => 3, 'language' => 'fr', 'name' => 'Enfant 3'],
            ]],
            ['id' => 16, 'company_id' => 3, 'language' => 'es', 'name' => 'Raiz 1', 'children' => [
                ['id' => 17, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 1'],
                ['id' => 18, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 2', 'children' => [
                    ['id' => 19, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 2.1'],
                ]],
                ['id' => 20, 'company_id' => 3, 'language' => 'es', 'name' => 'Hijo 3'],
            ]],
        ];

        return $data;
    }
}
