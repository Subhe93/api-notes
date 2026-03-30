<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;

class SharesController extends Controller
{
    public function index(Request $request)
    {
        $shares = $request->user()->shares()->with('sharedWith')->get();

        return response()->json([
            'shares' => $shares->map(function ($share) {
                return [
                    'id' => $share->id,
                    'type' => $share->type,
                    'value' => $share->value,
                    'permissions' => $share->permissions,
                    'sharedWith' => $share->sharedWith->email,
                    'sharedWithName' => $share->sharedWith->name,
                ];
            }),
        ]);
    }

    public function received(Request $request)
    {
        $shares = $request->user()->sharedWithMe()->with('owner')->get();

        return response()->json([
            'shares' => $shares->map(function ($share) {
                return [
                    'id' => $share->id,
                    'type' => $share->type,
                    'value' => $share->value,
                    'permissions' => $share->permissions,
                    'owner' => $share->owner->email,
                    'ownerName' => $share->owner->name,
                ];
            }),
        ]);
    }

    public function shareByDomain(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'email' => 'required|email',
            'permissions' => 'required|in:read,write',
        ]);

        return $this->createShare($request, 'domain', $request->domain);
    }

    public function shareByTag(Request $request)
    {
        $request->validate([
            'tag' => 'required|string|max:255',
            'email' => 'required|email',
            'permissions' => 'required|in:read,write',
        ]);

        return $this->createShare($request, 'tag', $request->tag);
    }

    private function createShare(Request $request, string $type, string $value)
    {
        $user = $request->user();
        
        // Find user to share with
        $sharedWithUser = User::where('email', $request->email)->first();
        
        if (!$sharedWithUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($sharedWithUser->id === $user->id) {
            return response()->json(['error' => 'Cannot share with yourself'], 400);
        }

        // Create or update share
        $share = Share::updateOrCreate(
            [
                'owner_id' => $user->id,
                'shared_with_id' => $sharedWithUser->id,
                'type' => $type,
                'value' => $value,
            ],
            [
                'permissions' => $request->permissions,
            ]
        );

        return response()->json([
            'success' => true,
            'share' => [
                'id' => $share->id,
                'type' => $share->type,
                'value' => $share->value,
                'permissions' => $share->permissions,
                'sharedWith' => $sharedWithUser->email,
            ],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $share = $request->user()->shares()->find($id);

        if (!$share) {
            return response()->json(['error' => 'Share not found'], 404);
        }

        $share->delete();

        return response()->json(['success' => true]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('q', '');
        
        $usersQuery = User::query();
        
        if (strlen($query) >= 2) {
            $usersQuery->where(function ($q) use ($query) {
                $q->where('email', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%');
            });
        }
        
        $users = $usersQuery->limit(20)->get(['id', 'name', 'email', 'avatar']);

        return response()->json([
            'users' => $users,
        ]);
    }

    public function sharedNotes(Request $request)
    {
        $user = $request->user();
        $shares = $user->sharedWithMe()->with('owner.notes')->get();
        
        $notes = [];
        
        foreach ($shares as $share) {
            $ownerNotes = $share->owner->notes;
            
            foreach ($ownerNotes as $note) {
                $include = false;
                
                if ($share->type === 'domain') {
                    $domain = parse_url($note->url, PHP_URL_HOST);
                    $include = $domain === $share->value;
                } elseif ($share->type === 'tag') {
                    $include = in_array($share->value, $note->tags ?? []);
                }
                
                if ($include) {
                    $notes[] = [
                        'url' => $note->url,
                        'title' => $note->title,
                        'tags' => $note->tags ?? [],
                        'notes' => $note->notes_data ?? [],
                        'sharedBy' => $share->owner->email,
                        'permissions' => $share->permissions,
                    ];
                }
            }
        }

        return response()->json([
            'notes' => $notes,
        ]);
    }
}
