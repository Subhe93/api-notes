<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('url', 2048);
            $table->string('title', 500)->nullable();
            $table->json('tags')->nullable();
            $table->json('notes_data');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->rawIndex('url(255)', 'notes_url_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
