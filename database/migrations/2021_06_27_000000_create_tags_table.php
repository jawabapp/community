<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tag_group_id')->nullable();
            $table->string('hash_tag');
            $table->string('deep_link')->nullable();
            $table->string('topic')->nullable();
            $table->integer('posts_count')->default(0);
            $table->integer('followers_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tag_group_id');
            $table->index('hash_tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
