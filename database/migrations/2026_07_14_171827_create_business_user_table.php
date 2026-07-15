<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('owner');
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
        });

        // Every user currently has at most one business via users.business_id;
        // backfill that as their owner membership row so existing accounts
        // keep working unchanged once switching is introduced.
        DB::table('users')->whereNotNull('business_id')->get()->each(function ($user) {
            DB::table('business_user')->insert([
                'business_id' => $user->business_id,
                'user_id' => $user->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_user');
    }
};
