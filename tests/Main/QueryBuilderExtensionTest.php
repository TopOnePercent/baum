<?php

use Mockery as m;
use Baum\Tests\Main\UnitAbstract;
use Illuminate\Database\Query\Grammars\Grammar;
use Baum\Extensions\Query\Builder as QueryBuilder;

// @codingStandardsIgnoreLine
class QueryBuilderExtensionTest extends UnitAbstract
{
    protected function getBuilder()
    {
        $connection = m::mock('Illuminate\Database\Connection');
        $connection->shouldReceive('getTablePrefix')->andReturn('');
        $processor = m::mock('Illuminate\Database\Query\Processors\Processor');

        return new QueryBuilder($connection, new Grammar($connection), $processor);
    }

    public function testReorderBy()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->orderBy('email')->orderBy('age', 'desc')->reOrderBy('full_name', 'asc');
        $this->assertEquals('select * from "users" order by "full_name" asc', $builder->toSql());
    }

    public function testAggregatesRemoveOrderBy()
    {
        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('select')->once()->andReturn([['aggregate' => 1]]);
        $builder->getProcessor()->shouldReceive('processSelect')->once()
            ->andReturnUsing(function ($builder, $results) {
                return $results;
            });
        $results = $builder->from('users')->orderBy('age', 'desc')->count();
        $this->assertEquals(1, $results);

        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('select')->once()->andReturn(array(array('exists' => true)));
        $results = $builder->from('users')->orderBy('age', 'desc')->exists();
        $this->assertTrue($results);

        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('select')->once()->andReturn(array(array('aggregate' => 1)));
        $builder->getProcessor()->shouldReceive('processSelect')->once()
            ->andReturnUsing(function ($builder, $results) {
                return $results;
            });
        $results = $builder->from('users')->orderBy('age', 'desc')->max('id');
        $this->assertEquals(1, $results);

        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('select')->once()->andReturn(array(array('aggregate' => 1)));
        $builder->getProcessor()->shouldReceive('processSelect')->once()
            ->andReturnUsing(function ($builder, $results) {
                return $results;
            });
        $results = $builder->from('users')->orderBy('age', 'desc')->min('id');
        $this->assertEquals(1, $results);

        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('select')->once()->andReturn(array(array('aggregate' => 1)));
        $builder->getProcessor()->shouldReceive('processSelect')->once()
            ->andReturnUsing(function ($builder, $results) {
                return $results;
            });
        $results = $builder->from('users')->orderBy('age', 'desc')->sum('id');
        $this->assertEquals(1, $results);
    }
}
