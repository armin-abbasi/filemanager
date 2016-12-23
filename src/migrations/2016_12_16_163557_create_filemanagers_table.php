<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilemanagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filemanagers',function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('real_name');
            $table->string('type');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('extension')->nullable();
            $table->integer('downloads')->default(0)->nullable();
            $table->date('date');
            $table->integer('sub_files')->default(0)->nullable();
            $table->integer('sub_folders')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('filemanagers');
    }
}
