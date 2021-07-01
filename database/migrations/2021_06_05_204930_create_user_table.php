<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('user')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('user')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('user')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->string('fullname', 100);
            $table->string('username', 100)->unique()->index();
            $table->string('email', 100)->unique()->index();
            $table->string('phone', 100)->unique()->index();
            $table->string('password');
            $table->date('date_of_birth')->nullable();
            $table->longText('address')->nullable();
            $table->longText('image')->nullable();
            $table->longText('image_url')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 100)->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
