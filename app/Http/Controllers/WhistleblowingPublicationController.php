<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WhistleblowingPublicationController extends Controller
{
    public function __invoke(): View
    {
        return view('public.whistleblowing');
    }
}
