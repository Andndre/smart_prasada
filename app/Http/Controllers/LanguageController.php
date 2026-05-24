<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change the application language
     */
    public function changeLanguage(Request $request, $locale)
    {
        // Check if the locale is supported
        if (! \in_array($locale, ['id', 'en'])) {
            abort(400);
        }

        // Store the locale in session
        Session::put('locale', $locale);

        // Set the application locale
        App::setLocale($locale);

        // Queue a persistent cookie
        cookie()->queue(cookie()->forever('locale', $locale));

        return redirect()->back()->with('success', __('app.language_changed'));
    }
}
