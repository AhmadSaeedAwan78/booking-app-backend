<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->integer("no_of_seats")->default(1);
            $table->integer("clean_time")->default(10); // In Minutes
            $table->integer("slot_duration")->default(10); //in minutes
            $table->integer("allow_booking_before_days")->default("7");
            $table->unsignedBigInteger('business_administrator_id');
            $table->foreign('business_administrator_id')->references('id')->on('business_administrators')->onDelete('cascade');
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
        Schema::dropIfExists('services');
    }
}
