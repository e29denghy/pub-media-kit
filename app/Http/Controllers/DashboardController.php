<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\PublishJob;
use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, PlatformAdapterRegistry $registry): Response
    {
        $userId = $request->user()->id;

        return Inertia::render('Dashboard', [
            'adapters' => $registry->catalog(),
            'drafts' => Draft::query()
                ->where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'status', 'tags', 'updated_at']),
            'jobs' => PublishJob::query()
                ->where('user_id', $userId)
                ->with(['draft:id,title', 'targets'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
