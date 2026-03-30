<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_with_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['domain', 'tag']);
            $table->string('value'); // domain name or tag name
            $table->enum('permissions', ['read', 'write'])->default('read');
            $table->timestamps();
            
            $table->unique(['owner_id', 'shared_with_id', 'type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
