<?php

use App\Models\Project;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $projects = Project::whereNull('qr_code')->orWhere('qr_code', '')->get();

        foreach ($projects as $project) {
            try {
                $qrCode = QrCode::create($project->id);
                $writer = new PngWriter;
                $result = $writer->write($qrCode);
                $qrCodeData = 'data:image/png;base64,'.base64_encode($result->getString());

                $project->update(['qr_code' => $qrCodeData]);
            } catch (Exception $e) {
                Log::error('Failed to generate QR code for project', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Project::query()->update(['qr_code' => null]);
    }
};
