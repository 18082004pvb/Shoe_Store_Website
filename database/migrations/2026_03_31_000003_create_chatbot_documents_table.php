<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('url')->nullable();

            $table->string('source_type')->nullable(); // product, category, article, policy, faq
            $table->unsignedBigInteger('source_id')->nullable();

            $table->longText('content')->nullable();
            $table->text('short_content')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('source_type');
            $table->index('source_id');
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_documents');
    }
};