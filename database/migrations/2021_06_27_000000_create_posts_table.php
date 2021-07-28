<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id');
            $table->bigInteger('related_post_id')->nullable();
            $table->bigInteger('parent_post_id')->nullable();
            $table->string('class_type');
            $table->text('content');
            $table->boolean('is_status');
            $table->string('deep_link')->nullable();
            $table->json('interactions')->nullable();
            $table->integer('children_count')->default(0);
            $table->string('hash');
            $table->json('extra_info')->nullable();
            $table->string('topic')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_id');
            $table->index('related_post_id');
            $table->index('parent_post_id');
        });

        // Full Text Index
        DB::statement('ALTER TABLE posts ADD FULLTEXT fulltext_content_index (content)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
