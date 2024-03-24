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
        Schema::create('monetary_accounts', function (Blueprint $table) {
            $table->id();
            $table->text('display_name')->nullable();
            $table->text('description');
            $table->text('iban');
            $table->string('currency');
            $table->boolean('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monetary_accounts');
    }
};
