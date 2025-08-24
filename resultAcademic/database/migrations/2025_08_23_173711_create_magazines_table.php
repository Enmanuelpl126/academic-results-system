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
        Schema::create('magazines', function (Blueprint $table) {
           $table->unsignedBigInteger('id')->primary(); 
            $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->string('volume')->nullable();
            $table->string('doi')->nullable();
            $table->timestamps();
            $table->foreign('id')->references('id')->on('publications')->onDelete('cascade');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magazines');
    }
};
