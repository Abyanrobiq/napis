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
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nama tujuan (e.g., "Beli Mobil", "Liburan ke Bali")
            $table->text('description')->nullable(); // Deskripsi detail
            $table->decimal('target_amount', 15, 2); // Target jumlah yang ingin dicapai
            $table->decimal('current_amount', 15, 2)->default(0); // Jumlah yang sudah terkumpul
            $table->string('icon')->nullable(); // Icon emoji
            $table->string('color')->default('#10B981'); // Warna untuk UI
            $table->date('target_date')->nullable(); // Target tanggal tercapai
            $table->enum('status', ['active', 'completed', 'paused'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
