<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user->isStaff() => view('dashboard.staff', ['data' => $this->dashboard->forStaff($user)]),
            $user->isAgent() => view('dashboard.agent', ['data' => $this->dashboard->forAgent($user)]),
            $user->isPilgrim() => view('dashboard.pilgrim', ['data' => $this->dashboard->forPilgrim($user)]),
            $user->isGuide() => view('dashboard.guide', ['data' => $this->dashboard->forGuide($user)]),
            default => view('dashboard.staff', ['data' => $this->dashboard->forStaff($user)]),
        };
    }
}
