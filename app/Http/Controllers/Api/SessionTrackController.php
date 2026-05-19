<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionTrack;
use Illuminate\Http\Request;

class SessionTrackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'date' => 'required|date_format:Y-m-d\TH:i:s*',
        ]);

        // Verifica se a sessão já foi registrada
        if (SessionTrack::where('session_id', $validated['session_id'])->exists()) {
            return response()->json(['message' => 'Sessão já rastreada'], 200);
        }

        $sessionTrack = SessionTrack::create([
            'session_id' => $validated['session_id'],
            'latitude' => $validated['location']['latitude'] ?? null,
            'longitude' => $validated['location']['longitude'] ?? null,
            'date' => $validated['date'],
        ]);

        return response()->json([
            'message' => 'Sessão rastreada com sucesso',
            'data' => $sessionTrack
        ], 201);
    }

    public function count()
    {
        $totalSessions = SessionTrack::distinct('session_id')->count('session_id');
        $todaySessions = SessionTrack::whereDate('date', today())
            ->distinct('session_id')
            ->count('session_id');

        return response()->json([
            'total_unique_sessions' => $totalSessions,
            'today_sessions' => $todaySessions,
        ]);
    }
}
