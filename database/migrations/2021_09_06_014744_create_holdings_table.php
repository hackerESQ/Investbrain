<?php

use App\Models\Portfolio;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHoldingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Portfolio::class, 'portfolio_id')->onDelete('cascade');
            $table->string('symbol');
            $table->float('quantity', 12, 4);
            $table->float('average_cost_basis', 12, 4);
            $table->float('total_cost_basis', 12, 4)->nullable();
            $table->float('realized_gain_loss_dollars', 12, 4)->nullable();
            $table->float('dividends_earned', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holdings');
    }
}