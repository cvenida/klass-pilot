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
        Schema::rename('courses', 'learning_strands');
        Schema::rename('course_applications', 'learning_strand_applications');

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['course_id']);

            $table->renameColumn('course_id', 'learning_strand_id');

            $table->foreign('learning_strand_id')
                  ->references('id')
                  ->on('learning_strand')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('learning_strands', 'courses');
        Schema::rename('learning_strand_applications', 'course_applications');

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['learning_strand_id']);
            $table->renameColumn('learning_strand_id', 'course_id');
            $table->foreign('course_id')
                  ->references('id')
                  ->on('courses')
                  ->onDelete('cascade');
        });
    }
};