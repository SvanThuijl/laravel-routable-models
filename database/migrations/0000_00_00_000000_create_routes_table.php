<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->morphs('routable');

            $table->string('method')
                ->default('get')
                ->index();
            $table->string('path')
                ->index();

            $table->string('name');
            $table->string('locale')
                ->nullable();

            $table->string('controller');
            $table->string('action');

            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
