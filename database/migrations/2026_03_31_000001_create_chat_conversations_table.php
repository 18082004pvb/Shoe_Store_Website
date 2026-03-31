<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('page_type')->nullable(); // home, product, cart, order...
            $table->string('page_slug')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};