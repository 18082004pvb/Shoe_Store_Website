<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_conversation_id');
            $table->string('role', 20); // user, assistant, system
            $table->longText('content');
            $table->string('openai_response_id')->nullable();
            $table->timestamps();

            $table->foreign('chat_conversation_id')
                ->references('id')
                ->on('chat_conversations')
                ->onDelete('cascade');

            $table->index('chat_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};