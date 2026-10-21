<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublishJobRequest;
use App\Models\Draft;
use App\Models\PublishJob;
use App\Models\PublishTarget;
use App\Publishing\PublishJobPlanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublishJobController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Jobs/Index', [
            'jobs' => PublishJob::query()
                ->where('user_id', $request->user()->id)
                ->with(['draft:id,title', 'targets'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function store(StorePublishJobRequest $request, Draft $draft, PublishJobPlanner $planner): RedirectResponse
    {
        $combinationId = $request->input('publish_combination_id');
        $combination = $combinationId
            ? $request->user()->publishCombinations()->findOrFail($combinationId)
            : null;

        $job = $planner->plan(
            $request->user(),
            $draft,
            $request->input('platforms', []),
            $combination,
        );

        return redirect()
            ->route('jobs.show', $job)
            ->with('status', '发布任务已生成。浏览器线路停在待人工确认，没有代替你点击发表。');
    }

    public function show(PublishJob $job): Response
    {
        $this->authorize('view', $job);

        $job->load(['draft.mediaAssets', 'targets', 'combination']);

        return Inertia::render('Jobs/Show', [
            'job' => $job,
        ]);
    }

    public function confirm(PublishTarget $target, PublishJobPlanner $planner): RedirectResponse
    {
        $this->authorize('confirm', $target);

        $planner->confirm($target);

        return redirect()
            ->route('jobs.show', $target->publish_job_id)
            ->with('status', '已记录人工确认。系统没有在平台上点击发表。');
    }
}
