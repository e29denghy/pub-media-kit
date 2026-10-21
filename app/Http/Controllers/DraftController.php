<?php

namespace App\Http\Controllers;

use App\Enums\DraftStatus;
use App\Http\Requests\StoreDraftRequest;
use App\Http\Requests\UpdateDraftRequest;
use App\Models\Draft;
use App\Models\PublishCombination;
use App\Publishing\PlatformAdapterRegistry;
use App\Publishing\PublishJobPlanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class DraftController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Drafts/Index', [
            'drafts' => Draft::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(Request $request, PlatformAdapterRegistry $registry): Response
    {
        return Inertia::render('Drafts/Form', [
            'draft' => null,
            'platforms' => $registry->catalog(),
            'combinations' => $this->combinations($request),
        ]);
    }

    public function store(StoreDraftRequest $request, PublishJobPlanner $planner): RedirectResponse
    {
        $draft = Draft::query()->create([
            'user_id' => $request->user()->id,
            'title' => $request->string('title')->toString(),
            'body' => $request->string('body')->toString(),
            'body_format' => 'markdown',
            'tags' => $request->input('tags', []),
            'status' => DraftStatus::Draft,
        ]);

        $this->storeMedia($request, $draft);

        if ($request->boolean('queue')) {
            $job = $planner->plan(
                $request->user(),
                $draft,
                $request->input('platforms', []),
                $this->combination($request),
            );

            return redirect()
                ->route('jobs.show', $job)
                ->with('status', '草稿已保存，并生成了发布任务。尚未向任何平台发表。');
        }

        return redirect()
            ->route('drafts.show', $draft)
            ->with('status', '草稿已保存。');
    }

    public function show(Request $request, Draft $draft, PlatformAdapterRegistry $registry): Response
    {
        $this->authorize('view', $draft);

        $draft->load(['mediaAssets', 'publishJobs.targets']);

        return Inertia::render('Drafts/Show', [
            'draft' => $draft,
            'platforms' => $registry->catalog(),
            'combinations' => $this->combinations($request),
        ]);
    }

    public function edit(Request $request, Draft $draft, PlatformAdapterRegistry $registry): Response
    {
        $this->authorize('update', $draft);

        $draft->load('mediaAssets');

        return Inertia::render('Drafts/Form', [
            'draft' => $draft,
            'platforms' => $registry->catalog(),
            'combinations' => $this->combinations($request),
        ]);
    }

    public function update(UpdateDraftRequest $request, Draft $draft, PublishJobPlanner $planner): RedirectResponse
    {
        $draft->update([
            'title' => $request->string('title')->toString(),
            'body' => $request->string('body')->toString(),
            'tags' => $request->input('tags', []),
        ]);

        $this->removeMedia($draft, $request->input('remove_media', []));
        $this->storeMedia($request, $draft);

        if ($request->boolean('queue')) {
            $job = $planner->plan(
                $request->user(),
                $draft,
                $request->input('platforms', []),
                $this->combination($request),
            );

            return redirect()
                ->route('jobs.show', $job)
                ->with('status', '草稿已更新，并生成了发布任务。尚未向任何平台发表。');
        }

        return redirect()
            ->route('drafts.show', $draft)
            ->with('status', '草稿已更新。');
    }

    public function destroy(Draft $draft): RedirectResponse
    {
        $this->authorize('delete', $draft);

        $draft->load('mediaAssets');
        $draft->mediaAssets->each->deleteWithFile();
        $draft->delete();

        return redirect()
            ->route('drafts.index')
            ->with('status', '草稿已删除。');
    }

    private function combinations(Request $request): mixed
    {
        return PublishCombination::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'platforms', 'notes']);
    }

    private function combination(Request $request): ?PublishCombination
    {
        $id = $request->input('publish_combination_id');

        if (! $id) {
            return null;
        }

        return PublishCombination::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);
    }

    private function storeMedia(Request $request, Draft $draft): void
    {
        foreach ($request->file('media', []) as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('drafts/'.$draft->id, 'local');

            $draft->mediaAssets()->create([
                'user_id' => $draft->user_id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * @param  list<int|string>  $ids
     */
    private function removeMedia(Draft $draft, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $draft->mediaAssets()
            ->whereIn('id', $ids)
            ->get()
            ->each
            ->deleteWithFile();
    }
}
