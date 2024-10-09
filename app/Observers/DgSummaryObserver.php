<?php

namespace App\Observers;

use App\Models\DgSummary;

class DgSummaryObserver
{
    /**
     * Handle the DgSummary "creating" event.
     */
    public function creating(DgSummary $dgSummary): void
    {
        $dgSummary->gross = $dgSummary->net ? round($dgSummary->net * 1.23, 2) : 0;
    }

    /**
     * Handle the DgSummary "updating" event.
     */
    public function updating(DgSummary $dgSummary): void
    {
        $dgSummary->gross = $dgSummary->net ? round($dgSummary->net * 1.23, 2) : 0;
    }
}
