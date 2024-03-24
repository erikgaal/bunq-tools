<?php

use App\Models\ImportMonetaryAccount;
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
        Schema::create('import_payments', function (Blueprint $table) {
            $table->id();
            $table->jsonb('original_json');
            $table->text('original');
            $table->foreignId('monetary_account_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_payments');
    }
};
