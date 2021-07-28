<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagGroupFollowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_group_followers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tag_group_id');
            $table->bigInteger('account_id');
            $table->timestamps();

            $table->index('tag_group_id');
            $table->index('account_id');
            $table->index(['account_id', 'tag_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_group_followers');
    }
}
