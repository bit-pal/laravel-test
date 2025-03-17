<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class ScoringController extends Controller
{
    private $scoringService;

    public function __construct(ScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    public function getMatchedUsers(Request $request)
    {
        $user = $request->user();
        $userType = $user->user_type;

        $query = User::query();

        // Filter based on user type
        if ($userType === 3) { // Startup
            $query->whereIn('user_type', [1, 2]); // Show LPs and Investors
        } elseif ($userType === 2) { // LP/GP
            $query->whereIn('user_type', [1, 2]); // Show LPs and Investors
        } elseif ($userType === 1) { // Investor
            $query->whereIn('user_type', [1, 2, 3]); // Show all types
        }

        $users = $query->where('id', '!=', $user->id)->get();

        // Calculate scores and sort
        $scoredUsers = $users->map(function ($matchedUser) use ($user) {
            $score = $this->scoringService->calculateScore($user, $matchedUser);
            return [
                'user' => $matchedUser,
                'score' => $score
            ];
        })->sortBy('score')->values();

        return response()->json($scoredUsers);
    }

    public function getMatchedCoInvestors(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->user_type, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::where('id', '!=', $user->id)
            ->whereIn('user_type', [1, 2])
            ->get();

        // Calculate scores and sort
        $scoredUsers = $users->map(function ($matchedUser) use ($user) {
            $score = $this->scoringService->calculateScore($user, $matchedUser);
            return [
                'user' => $matchedUser,
                'score' => $score
            ];
        })->sortBy('score')->values();

        return response()->json($scoredUsers);
    }
} 