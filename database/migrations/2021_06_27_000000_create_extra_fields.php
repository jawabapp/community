<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtraFields extends Migration
{
    private $extra_fields = [
        'slug',
        'deep_link',
        'extra_info',
        'topic',
        'post_count',
        'followers_count',
        'following_count',
        'mutual_follower_count'
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        echo "Building extra fields is runnibg .....\n";

        $user_class = config('community.user_class');
        if (!empty($user_class)) {
            $userObject = new $user_class();
            if ($userObject instanceof \Illuminate\Database\Eloquent\Model) {

                foreach ($this->extra_fields as $field) {
                    if (!Schema::hasColumn($userObject->getTable(), $field)) {
                        Schema::table($userObject->getTable(), function (Blueprint $table) use ($field) {
                            $table->string($field)->nullable();
                        });
                        echo "column " . $field . " is added to  " . $userObject->getTable() . " table successfully ...\n";
                    } else {
                        echo "column " . $field . " is already exists on " . $userObject->getTable() . " table ...\n";
                    }
                }
            } else {
                echo "the class '" . config('community.user_class') . "' is not a valid model, please ensure to set correct model on community.php config file ...\n";
            }
        }

        echo "Building extra fields is finished .....\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
