<?php

namespace Baum\Tests\Suite\Support;

trait Cast
{
    /**
     * Cast provided keys's values into ints. This is to wrestle with PDO driver
     * inconsistencies.
     *
     * @param array $input
     * @param mixed $keys
     *
     * @return array
     */
    public function array_ints_keys(array $input, $keys = 'id')
    {
        $keys = is_string($keys) ? [$keys] : $keys;
        array_walk_recursive($input, function (&$value, $key) use ($keys) {
            if (array_search($key, $keys) !== false) {
                $value = (int) $value;
            }
        });

        return $input;
    }
}
