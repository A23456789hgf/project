<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectRequest;
use ArPHP\I18N\Arabic;

class ProjectNumberGenerator
{
    /**
     * Get current Hijri year using ArPHP
     */
    private static function getHijriYear(): string
    {
        try {
            $arabic = new Arabic;

            return $arabic->date('Y', time());
        } catch (\Exception $e) {
            // Fallback to Gregorian year if library fails
            return date('Y');
        }
    }

    /**
     * Generate project request number
     * New Format: REQYYYY#### (e.g. REQ14470001)
     * REQ = Request identifier
     * YYYY = Hijri Year
     * #### = Sequential number (4 digits)
     */
    public static function generateRequestNumber(): string
    {
        $year = self::getHijriYear();

        // Match both old (MAFWRREQ...) and new (REQ...) formats for transition if needed,
        // but prefer searching for the new format first.
        $lastRequest = ProjectRequest::withoutGlobalScopes()
            ->where('request_number', 'like', "REQ{$year}%")
            ->orderByRaw('CAST(SUBSTR(request_number, -4) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->request_number, -4);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('REQ%s%04d', $year, $nextNumber);
    }

    /**
     * Generate final project number after approval
     * New Format: PROYYYY#### (e.g. PRO14470017)
     * PRO = Project identifier
     * YYYY = Hijri Year
     * #### = Sequential number (4 digits)
     */
    public static function generateProjectNumber(?string $year = null): string
    {
        $year = $year ?: self::getHijriYear();

        $lastProject = Project::withoutGlobalScopes()
            ->where('form_number', 'like', "PRO{$year}%")
            ->orderByRaw('CAST(SUBSTR(form_number, -4) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastProject) {
            $lastNumber = (int) substr($lastProject->form_number, -4);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('PRO%s%04d', $year, $nextNumber);
    }

    /**
     * Generate request number with fallback to ProjectRequest model if service fails
     */
    public static function getNextRequestNumber(?string $year = null): string
    {
        try {
            return self::generateRequestNumber();
        } catch (\Exception $e) {
            \Log::error('Failed to generate request number: '.$e->getMessage());

            return 'REQ'.date('Y').'0001';
        }
    }

    /**
     * Generate project number with fallback
     */
    public static function getNextProjectNumber(?string $year = null): string
    {
        try {
            return self::generateProjectNumber($year);
        } catch (\Exception $e) {
            \Log::error('Failed to generate project number: '.$e->getMessage());
            $year = $year ?: self::getHijriYear();

            return sprintf('PRO%s%04d', $year, 1);
        }
    }
}
