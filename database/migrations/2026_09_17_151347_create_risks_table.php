<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('impact', ['low', 'medium', 'high']);
            $table->enum('probability', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'monitoring', 'mitigated', 'closed'])->default('open');
            $table->text('mitigation_plan')->nullable();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
