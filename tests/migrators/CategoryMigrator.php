<?php

use Illuminate\Support\Facades\Schema;

class CategoryMigrator
{
    public function up()
    {
        Schema::dropIfExists('categories');

        Schema::create('categories', function ($t) {
            $t->increments('id');

            $t->integer('parent_id')->nullable();
            $t->integer('lft')->nullable();
            $t->integer('rgt')->nullable();
            $t->integer('depth')->nullable();

            $t->string('name');

            $t->integer('company_id')->unsigned()->nullable();
            $t->string('language', 3)->nullable();

            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();

            $t->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('categories');
    }
}
