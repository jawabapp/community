<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraAccountFields extends Migration
{

    protected $table_name;

    public function __construct()
    {
        $this->table_name = app(config('community.user_class'))->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->string('deep_link')->nullable();
            $table->json('extra_info')->nullable();
            $table->string('topic')->nullable();
            $table->integer('post_count')->default(0);
            $table->integer('followers_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->integer('mutual_follower_count')->default(0);

            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('deep_link');
            $table->dropColumn('extra_info');
            $table->dropColumn('topic');
            $table->dropColumn('post_count');
            $table->dropColumn('followers_count');
            $table->dropColumn('following_count');
            $table->dropColumn('mutual_follower_count');

            $table->dropIndex('slug');
        });
    }
}
