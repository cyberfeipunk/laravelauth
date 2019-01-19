<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_accounts', function (Blueprint $table) {
            $table->increments('account_id');
            $table->integer('member_id');
            $table->enum('login_type',['local','mobile','email']);
            $table->string('login_account')->unique();
            $table->string('login_password','60');
            $table->string('account_token','60');
            $table->string('token_ip','20');
            $table->time('token_expired');
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
        Schema::dropIfExists('member_accounts');
    }
}
