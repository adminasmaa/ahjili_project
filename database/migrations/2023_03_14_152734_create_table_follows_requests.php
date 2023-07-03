<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follows_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_follow_id');
            $table->foreignId('user_id')->constrained();
            $table->boolean('has_request_follow')->default(true);
            $table->boolean('is_accepted')->default(false);
            $table->dateTime('accepted_at')->nullable();
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
        Schema::dropIfExists('follows_requests');
    }
};
