<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); //Not required when using OAuth
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('occupation')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->rememberToken(); //on login will updated
            $table->timestamps(); //will created_at & updated_at. These field will autofield on addition/change
            $table->softDeletes(); //for deleted_at
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