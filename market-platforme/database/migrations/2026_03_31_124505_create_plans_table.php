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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
             $table->string('name');
    $table->string('code')->unique();
    $table->decimal('price', 10, 2)->default(0);
    $table->string('interval')->nullable(); // monthly, yearly
    $table->boolean('allows_download')->default(false);
    $table->boolean('allows_dashboard')->default(false);
    $table->integer('reports_quota')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
