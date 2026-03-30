<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function index(Request $request)
    {
        $notes = $request->user()->notes()->get();

        return response()->json([
            'notes' => $notes->map(function ($note) {
                return [
                    'url' => $note->url,
                    'title' => $note->title,
                    'tags' => $note->tags ?? [],
                    'notes' => $note->notes_data ?? [],
                    'updatedAt' => $note->updated_at->toISOString(),
                ];
            }),
        ]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'notes' => 'present|array',
        ]);

        $user = $request->user();
        $clientNotes = $request->notes;
        $serverNotes = $user->notes()->get()->keyBy('url');
        $result = [];

        foreach ($clientNotes as $clientNote) {
            $url = $clientNote['url'] ?? null;
            if (!$url) continue;

            $serverNote = $serverNotes->get($url);
            $clientUpdatedAt = isset($clientNote['updatedAt']) ? strtotime($clientNote['updatedAt']) : 0;

            if ($serverNote) {
                $serverUpdatedAt = $serverNote->updated_at->timestamp;

                // Client is newer - update server
                if ($clientUpdatedAt > $serverUpdatedAt) {
                    $serverNote->update([
                        'title' => $clientNote['title'] ?? null,
                        'tags' => $clientNote['tags'] ?? [],
                        'notes_data' => $clientNote['notes'] ?? [],
                        'synced_at' => now(),
                    ]);
                }

                // Add to result (use latest)
                $latest = $clientUpdatedAt > $serverUpdatedAt ? $clientNote : [
                    'url' => $serverNote->url,
                    'title' => $serverNote->title,
                    'tags' => $serverNote->tags ?? [],
                    'notes' => $serverNote->notes_data ?? [],
                    'updatedAt' => $serverNote->updated_at->toISOString(),
                ];
                $result[] = $latest;
            } else {
                // New note from client
                $note = $user->notes()->create([
                    'url' => $url,
                    'title' => $clientNote['title'] ?? null,
                    'tags' => $clientNote['tags'] ?? [],
                    'notes_data' => $clientNote['notes'] ?? [],
                    'synced_at' => now(),
                ]);
                $result[] = $clientNote;
            }

            $serverNotes->forget($url);
        }

        // Add server-only notes to result
        foreach ($serverNotes as $serverNote) {
            $result[] = [
                'url' => $serverNote->url,
                'title' => $serverNote->title,
                'tags' => $serverNote->tags ?? [],
                'notes' => $serverNote->notes_data ?? [],
                'updatedAt' => $serverNote->updated_at->toISOString(),
            ];
        }

        return response()->json([
            'success' => true,
            'notes' => $result,
            'syncedAt' => now()->toISOString(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|string|max:2048',
            'title' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);

        $note = $request->user()->notes()->updateOrCreate(
            ['url' => $request->url],
            [
                'title' => $request->title,
                'tags' => $request->tags ?? [],
                'notes_data' => $request->notes ?? [],
                'synced_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'note' => [
                'url' => $note->url,
                'title' => $note->title,
                'tags' => $note->tags ?? [],
                'notes' => $note->notes_data ?? [],
                'updatedAt' => $note->updated_at->toISOString(),
            ],
        ]);
    }

    public function destroy(Request $request, $url)
    {
        $request->user()->notes()->where('url', urldecode($url))->delete();

        return response()->json(['success' => true]);
    }
}
