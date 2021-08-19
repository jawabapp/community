<?php

namespace Jawabapp\Community\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SetupExtraFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:extra-fields';

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
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Extra Fields';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
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
}
