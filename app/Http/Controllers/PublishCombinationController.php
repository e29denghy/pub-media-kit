<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublishCombinationRequest;
use App\Models\PublishCombination;
use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublishCombinationController extends Controller
{
    public function index(Request $request, PlatformAdapterRegistry $registry): Response
    {
        return Inertia::render('Combinations/Index', [
            'combinations' => PublishCombination::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get(),
            'platforms' => $registry->catalog(),
        ]);
    }

    public function store(StorePublishCombinationRequest $request): RedirectResponse
    {
        $request->user()->publishCombinations()->create([
            'name' => $request->string('name')->toString(),
            'notes' => $request->input('notes'),
            'platforms' => array_values($request->input('platforms', [])),
        ]);

        return redirect()
            ->route('combinations.index')
            ->with('status', '平台组合已保存。');
    }

    public function destroy(PublishCombination $combination): RedirectResponse
    {
        $this->authorize('delete', $combination);
        $combination->delete();

        return redirect()
            ->route('combinations.index')
            ->with('status', '平台组合已删除。已有发布任务仍保留当时的平台快照。');
    }
}
