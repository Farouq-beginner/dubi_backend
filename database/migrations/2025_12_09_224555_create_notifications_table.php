<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type')->nullable(); 
            $table->json('extra')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
