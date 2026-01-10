<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJourToSeancesTable extends Migration
{
    public function up()
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->string('jour')->nullable()->after('groupe');
        });
    }
    
    public function down()
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropColumn('jour');
        });
    }
}