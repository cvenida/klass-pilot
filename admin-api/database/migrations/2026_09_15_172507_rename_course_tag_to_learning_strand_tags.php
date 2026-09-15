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
        Schema::table('learning_strand', function (Blueprint $table) {
            $table->renameColumn('course_tags', 'learning_strand_tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_strand', function (Blueprint $table) {
            $table->renameColumn('learning_strand_tags', 'course_tags');
        });
    }
};