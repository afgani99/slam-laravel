<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $locale = $request->input('locale');
        if (in_array($locale, ['id', 'en'])) {
            Session::put('locale', $locale);
        }
        return redirect()->back();
    }
}
