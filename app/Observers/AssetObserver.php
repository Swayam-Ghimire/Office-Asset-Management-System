<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetLog;
use Illuminate\Support\Facades\Auth;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id() ?? null,
            'action' => 'created',
            'remarks' => 'Created by '. Auth::user() ? Auth::user()->id : 'Import by Excel file',
        ]);
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
            'remarks' => 'Updated by '.Auth::user()->name,
        ]);
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'remarks' => 'Soft-deleted by '.Auth::user()->name,
        ]);
    }

    /**
     * Handle the Asset "restored" event.
     */
    public function restored(Asset $asset): void
    {
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'restored',
            'remarks' => 'Restored from trash by '.Auth::user()->name,
        ]);
    }

    /**
     * Handle the Asset "force deleted" event.
     */
    public function forceDeleted(Asset $asset): void
    {
        //
    }
}
