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
        Schema::create('chapters', function (Blueprint $table) {
           $table->unsignedBigInteger('id')->primary(); 
            $table->string('book_name');
            $table->string('author')->nullable();
            $table->string('editorial')->nullable();
            $table->string('place')->nullable();
            $table->timestamps();
            $table->foreign('id')->references('id')->on('publications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
