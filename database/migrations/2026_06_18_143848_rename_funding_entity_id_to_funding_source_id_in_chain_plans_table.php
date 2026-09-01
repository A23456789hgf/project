<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameFundingEntityIdToFundingSourceIdInChainPlansTable extends Migration
{
    public function up()
    {
        Schema::table('chain_plans', function (Blueprint $table) {
            $table->renameColumn('funding_entity_id', 'funding_source_id');
        });
    }

    public function down()
    {
        Schema::table('chain_plans', function (Blueprint $table) {
            $table->renameColumn('funding_source_id', 'funding_entity_id');
        });
    }
}
