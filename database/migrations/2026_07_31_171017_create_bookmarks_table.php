<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('url', 2048);
            $table->string('url_hash', 64)->index();
            $table->text('description')->nullable();
            $table->string('favicon')->nullable();
            $table->longText('notes')->nullable();

            $table->timestamp('pinned_at')->nullable();
            $table->timestamp('favorited_at')->nullable();
            $table->timestamp('read_later_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('last_checked_at')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->boolean('is_broken')->default(false);

            $table->unsignedInteger('visits_count')->default(0);
            $table->timestamp('last_visited_at')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
