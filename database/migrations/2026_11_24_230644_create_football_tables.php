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
        Schema::create('competitions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('code', 20)->nullable()->index();
            $table->string('type', 50)->nullable();
            $table->string('area')->nullable();
            $table->string('emblem', 500)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->index();
            $table->string('short_name')->nullable();
            $table->string('tla', 10)->nullable();
            $table->string('crest', 500)->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->unsignedSmallInteger('founded')->nullable();
            $table->string('club_colors')->nullable();
            $table->string('venue')->nullable();
            $table->timestamps();
        });

        Schema::create('competition_team', function (Blueprint $table) {
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('team_id');
            $table->primary(['competition_id', 'team_id']);

            $table->foreign('competition_id')->references('id')->on('competitions')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('name')->index();
            $table->string('position', 50)->nullable()->index();
            $table->unsignedSmallInteger('shirt_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });

        Schema::create('league_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('competition_id')->primary();
            $table->string('status', 30);
            $table->string('message', 500)->nullable();
            $table->unsignedInteger('retry_after_seconds')->nullable();
            $table->unsignedInteger('teams_imported')->nullable();
            $table->unsignedInteger('players_imported')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_imports');
        Schema::dropIfExists('players');
        Schema::dropIfExists('competition_team');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('competitions');
    }
};
