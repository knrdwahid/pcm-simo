<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('#006837');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('image_url')->nullable();
            $table->text('arabic_quote')->nullable();
            $table->string('status')->default('published'); // published, draft
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position'); // e.g. Ketua, Sekretaris, Bendahara, dsb.
            $table->string('period')->default('2022 - 2027');
            $table->string('image_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('aums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // Pendidikan, Kesehatan, Sosial, Masjid
            $table->string('address')->nullable();
            $table->string('leader')->nullable();
            $table->string('phone')->nullable();
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aums');
        Schema::dropIfExists('officials');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('categories');
    }
};
