<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXFieldsTable extends Migration
{
    /**
     * Запустить миграции.
     * @return void
     */
    public function up()
    {
        Schema::create('x_fields', function (Blueprint $table) {

            $table->id();
            $table->string('extensible', 30);
            $table->string('name', 30);
            $table->string('type', 20)->default('string');
            $table->text('params')->nullable();
            $table->string('title', 125)->nullable();
            $table->text('descr')->length(500)->nullable();
            $table->text('html_flags')->length(500)->nullable();

            // disabled or except or used or activated.

            $table->index('extensible');
            $table->unique(['extensible', 'name']);

            $table->timestamps();
        });
    }

    /**
     * Обратить миграции.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('x_fields');
    }
}
