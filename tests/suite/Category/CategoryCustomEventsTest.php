<?php

use Illuminate\Support\Facades\Event;

class CategoryCustomEventsTest extends CategoryTestCase
{
    public function setUp()
    {
        parent::setUp();

        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Category::setEventDispatcher($initialDispatcher);
    }

    public function testMovementEventsFire()
    {
        $child = $this->categories('Child 1');

        $child->moveToRightOf($this->categories('Child 3'));

        // $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($child), $child)->andReturn(true);
        // $events->shouldReceive('fire')->once()->with('eloquent.moved: '.get_class($child), $child)->andReturn(true);

        Event::assertDispatched('eloquent.moving: '.Category::class, function ($event) use ($child) {
            // dd($event, $child);
           // return $e->event === $event;
        });

        Event::assertDispatched('eloquent.moved: '.Category::class, function ($event) use ($child) {
            // dd($event, $child);
           // return $e->event === $event;
        });
    }

    public function testMovementHaltsWhenReturningFalseFromMoving()
    {
        $unchanged = $this->categories('Child 2');

        $dispatcher = Category::getEventDispatcher();

        Category::setEventDispatcher($events = m::mock('Illuminate\Events\Dispatcher[until]'));

        $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($unchanged), $unchanged)->andReturn(false);

        // Force "moving" to return false
        Category::moving(function ($node) {
            return false;
        });

        $unchanged->makeRoot();

        $unchanged->reload();

        $this->assertEquals(1, $unchanged->getParentId());
        $this->assertEquals(1, $unchanged->getLevel());
        $this->assertEquals(4, $unchanged->getLeft());
        $this->assertEquals(7, $unchanged->getRight());

        // Restore
        Category::getEventDispatcher()->forget('eloquent.moving: '.get_class($unchanged));

        Category::unsetEventDispatcher();
        Category::setEventDispatcher($dispatcher);
    }
}
