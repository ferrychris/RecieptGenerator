<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('invoices')->where('status', 'draft')->update(['status' => 'unpaid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('invoices')->where('status', 'unpaid')->update(['status' => 'draft']);
    }
};
