<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMercadoTransactions extends Migration
{
    public function up()
    {
        Schema::create('mercado_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('trans')->nullable(false);
            $table->smallInteger('state')->nullable(false);
            $table->string('pref_id')->nullable(true);
            $table->string('payment_id')->nullable(true);
            $table->float('total_paid_amount')->nullable(true);
            $table->float('net_received_amount')->nullable(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mercado_transactions');
    }
}
