<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statements', function (Blueprint $table) {
            $table->increments('id');

            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();

            $table->string('accountId')->nullable();
            $table->string('visitId')->nullable();

            $table->string('accountClass')->nullable();
            $table->string('attendingPhysician')->nullable();

            $table->date('serviceDate')->nullable();
            $table->date('serviceDateTo')->nullable();

            $table->decimal('totalCharges', 10, 2)->nullable();
            $table->decimal('totalPayments', 10, 2)->nullable();
            $table->decimal('totalBalance', 10, 2)->nullable();

            $table->integer('totalPages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statements');
    }
};
