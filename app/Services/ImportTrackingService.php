<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\ImportLogRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportTrackingService
{
    /**
     * Start tracking an import operation.
     */
    public function startImport(string $unitName, string $modelClass, string $fileName): ImportLog
    {
        return ImportLog::create([
            'unit_name' => $unitName,
            'model_class' => $modelClass,
            'file_name' => $fileName,
            'user_id' => Auth::id(),
            'status' => 'Processing',
            'started_at' => now(),
            'total_records' => 0,
            'successful_records' => 0,
            'failed_records' => 0,
            'errors' => [],
        ]);
    }

    /**
     * Record a successful row import (either created or updated).
     */
    public function recordSuccess(ImportLog $importLog, $modelInstance, string $action = 'created')
    {
        if ($modelInstance) {
            ImportLogRecord::create([
                'import_log_id' => $importLog->id,
                'model_type' => get_class($modelInstance),
                'model_id' => $modelInstance->id,
                'action' => $action,
                'original_data' => null, // Can be used later to store original data for updates
            ]);
        }
    }

    /**
     * Finish the import operation, saving the final counts and errors.
     */
    public function finishImport(ImportLog $importLog, int $total, int $successful, int $failed, array $errors)
    {
        $status = 'Success';
        if ($failed > 0 && $successful > 0) {
            $status = 'Partial';
        } elseif ($failed > 0 && $successful === 0) {
            $status = 'Failed';
        }

        $importLog->update([
            'total_records' => $total,
            'successful_records' => $successful,
            'failed_records' => $failed,
            'status' => $status,
            'errors' => empty($errors) ? null : $errors,
            'completed_at' => now(),
        ]);
    }

    /**
     * Rollback an import operation by deleting all created records.
     */
    public function rollback(ImportLog $importLog)
    {
        DB::beginTransaction();
        try {
            // Find all records that were 'created' by this import
            $recordsToRollback = $importLog->records()->where('action', 'created')->get();

            foreach ($recordsToRollback as $record) {
                $modelClass = $record->model_type;
                $modelId = $record->model_id;

                // Safely find and delete the model
                if (class_exists($modelClass)) {
                    $instance = $modelClass::find($modelId);
                    if ($instance) {
                        // Force delete if the user meant to completely undo it,
                        // or regular delete if the model uses soft deletes.
                        // We will use regular delete to respect soft deletes if the model uses them,
                        // or forceDelete if we want a hard rollback.
                        // Usually rollback implies hard delete. We will use forceDelete if method exists.
                        if (method_exists($instance, 'forceDelete')) {
                            $instance->forceDelete();
                        } else {
                            $instance->delete();
                        }
                    }
                }
            }

            // Update log status
            $importLog->update([
                'status' => 'Rolled Back',
                'rolled_back_at' => now(),
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rollback Import failed: '.$e->getMessage());
            throw $e;
        }
    }
}
