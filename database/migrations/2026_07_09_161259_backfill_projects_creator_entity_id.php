<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill creator_entity_id in projects table
     * by matching created_by_entity (text) to internal_entities.name
     */
    public function up(): void
    {
        $projects = DB::table('projects')
            ->whereNull('creator_entity_id')
            ->whereNotNull('created_by_entity')
            ->where('created_by_entity', '!=', '')
            ->get();

        $manualMapping = [
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بأمانة العاصمة' => 196,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة صنعاء' => 197,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة ذمار' => 198,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة البيضاء' => 199,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة ريمة' => 200,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة حجة' => 201,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة المحويت' => 202,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة صعدة' => 203,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة الحديدة' => 204,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة عمران' => 205,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة الجوف' => 206,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة مارب' => 207,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة تعز' => 208,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة اب' => 209,
            'وحدة تمويل المشاريع والمبادرات الزراعية والسمكية بمحافظة الضالع' => 210,
            'وحدة التمويل المركزية' => 48,
        ];

        foreach ($projects as $project) {
            $rawName = $project->created_by_entity;
            $cleanName = preg_replace('/^[\s\x00-\x1F\x7F\xA0\x{00A0}\x{200B}\x{FEFF}]+|[\s\x00-\x1F\x7F\xA0\x{00A0}\x{200B}\x{FEFF}]+$/u', '', $rawName);

            if (isset($manualMapping[$cleanName])) {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['creator_entity_id' => $manualMapping[$cleanName]]);

                continue;
            }

            $match = DB::table('internal_entities')->where('name', $cleanName)->first();
            if ($match) {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['creator_entity_id' => $match->id]);
            }
        }
    }

    public function down(): void
    {
        // Only reset records that were backfilled from the text field
        DB::table('projects')
            ->whereNotNull('created_by_entity')
            ->where('created_by_entity', '!=', '')
            ->update(['creator_entity_id' => null]);
    }
};
