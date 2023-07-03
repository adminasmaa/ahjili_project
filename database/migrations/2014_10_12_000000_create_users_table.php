<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('username')->unique()->nullable();
            $table->string('country_code');
            $table->string('phone_number')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('dob');
            $table->string('gender')->nullable();
            $table->string('website')->nullable();
            $table->string('job')->nullable();
            $table->string('description')->nullable();
            $table->string('profile_image')->before('created_at')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('status')->default(1);
            $table->boolean('active_status')->default(true);
            $table->text('fcmtoken')->nullable();
            $table->string('account_type')->default('public');
            $table->rememberToken();
            $table->softDeletes();
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
        Schema::dropIfExists('users');
    }
}
