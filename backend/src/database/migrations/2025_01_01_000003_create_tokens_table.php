<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->integer('token_number');
            $table->string('customer_name');
            $table->text('customer_phone_encrypted');
            $table->enum('status', ['waiting','called','served','cancelled','no_show'])->default('waiting');
            $table->integer('estimated_wait')->default(0);
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();

            $table->index(['clinic_id','status','created_at']);
            $table->index(['clinic_id','service_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
