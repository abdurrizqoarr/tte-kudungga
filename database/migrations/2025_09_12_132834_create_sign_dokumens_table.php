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
        Schema::create('sign_dokumen', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("nik");
            $table->string("dokumen_asli");
            $table->string("id_dokumen_ttd");
            $table->string("image_ttd");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sign_dokumen');
    }
};
