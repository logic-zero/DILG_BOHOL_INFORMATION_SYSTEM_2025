<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 50);
            $table->timestamps();
        });

        // Add index for performance optimization
        Schema::table('page_visits', function (Blueprint $table) {
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
