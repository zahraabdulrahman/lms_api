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
        Schema::create('users', function (Blueprint $table) {

            $table->id();
            $table->string('name');
            $table->string('email')->unique(); //email has to be uniqu
            $table->string('password'); 
            $table->decimal('price', 8, 2)->nullable(); 
            $table->date('start_date')->nullable(); 
            $table->date('end_date')->nullable(); 
            $table->text('details')->nullable();
            $table->string('instructor_name')->nullable(); 
            $table->string('role')->default('student'); //add role
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
        
};
