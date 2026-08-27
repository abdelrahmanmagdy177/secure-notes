<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Native\Mobile\Facades\Biometrics;
use Native\Mobile\Facades\Haptics;
use Native\Mobile\Facades\System;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $isAuthenticated = (bool) $request->session()->get('biometric_auth', false);

        $publicNotes = $this->getNotes(isPrivate: false);
        $privateNotes = $isAuthenticated ? $this->getNotes(isPrivate: true) : [];

        return view('notes', [
            'native' => System::isMobile(),
            'authenticated' => $isAuthenticated,
            'publicNotes' => $publicNotes,
            'privateNotes' => $privateNotes,
        ]);
    }

    /**
     * Authenticate via Fingerprint / Biometrics hardware.
     */
    public function authenticate(Request $request): JsonResponse
    {
        if (! System::isMobile()) {
            $request->session()->put('biometric_auth', true);

            return response()->json([
                'success' => true,
                'simulated' => true,
                'privateNotes' => $this->getNotes(isPrivate: true),
            ]);
        }

        try {
            $prompt = Biometrics::prompt();
            $success = $prompt->prompt();

            if ($success) {
                $request->session()->put('biometric_auth', true);
                Haptics::vibrate();

                return response()->json([
                    'success' => true,
                    'simulated' => false,
                    'privateNotes' => $this->getNotes(isPrivate: true),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Fingerprint not recognized',
                'simulated' => false,
            ], 401);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'simulated' => false,
            ], 500);
        }
    }

    /**
     * Lock the private vault.
     */
    public function lock(Request $request): JsonResponse
    {
        $request->session()->forget('biometric_auth');

        return response()->json(['success' => true]);
    }

    /**
     * Create a new public or private note or secret key.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_private' => 'required|boolean',
            'type' => 'nullable|string|in:note,secret',
            'category' => 'nullable|string|max:50',
            'secret_value' => 'nullable|string',
        ]);

        $isPrivate = (bool) $validated['is_private'];

        // Guard private items with biometric session verification
        if ($isPrivate && ! $request->session()->get('biometric_auth', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Fingerprint authentication required to create private secrets/notes.',
            ], 403);
        }

        $type = $validated['type'] ?? 'note';

        $note = [
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'type' => $type,
            'category' => $validated['category'] ?? ($type === 'secret' ? 'API' : 'General'),
            'secret_value' => $type === 'secret' ? ($validated['secret_value'] ?? '') : null,
            'is_private' => $isPrivate,
            'created_at' => now()->format('M j, Y • g:i A'),
        ];

        $notes = $this->getNotes($isPrivate);
        array_unshift($notes, $note);
        $this->saveNotes($isPrivate, $notes);

        if (System::isMobile()) {
            Haptics::vibrate();
        }

        return response()->json([
            'success' => true,
            'note' => $note,
        ]);
    }

    /**
     * Delete a note by ID.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $isPrivate = $request->boolean('is_private');

        if ($isPrivate && ! $request->session()->get('biometric_auth', false)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notes = $this->getNotes($isPrivate);
        $notes = array_values(array_filter($notes, fn ($n) => $n['id'] !== $id));
        $this->saveNotes($isPrivate, $notes);

        return response()->json(['success' => true]);
    }

    private function getNotes(bool $isPrivate): array
    {
        $file = $isPrivate ? 'notes/private.json' : 'notes/public.json';
        if (! Storage::exists($file)) {
            // Seed initial sample note if file doesn't exist
            $initial = $isPrivate ? [
                [
                    'id' => (string) Str::uuid(),
                    'title' => 'Secret Vault Entry',
                    'content' => 'This note is protected by your fingerprint sensor. Only you can read or edit it!',
                    'is_private' => true,
                    'created_at' => now()->format('M j, Y • g:i A'),
                ],
            ] : [
                [
                    'id' => (string) Str::uuid(),
                    'title' => 'Welcome to Public Notes',
                    'content' => 'Anyone opening the app can see public notes. Switch to Private Notes for Fingerprint protection!',
                    'is_private' => false,
                    'created_at' => now()->format('M j, Y • g:i A'),
                ],
            ];
            Storage::put($file, json_encode($initial, JSON_PRETTY_PRINT));

            return $initial;
        }

        $content = Storage::get($file);

        return json_decode($content, true) ?: [];
    }

    private function saveNotes(bool $isPrivate, array $notes): void
    {
        $file = $isPrivate ? 'notes/private.json' : 'notes/public.json';
        Storage::put($file, json_encode($notes, JSON_PRETTY_PRINT));
    }
}
