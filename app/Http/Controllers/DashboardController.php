<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the signed-in user's account security overview.
     */
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'user' => $request->user(),
            'events' => $request->user()->securityEvents()->latest('id')->limit(10)->get(),
        ]);
    }
}
