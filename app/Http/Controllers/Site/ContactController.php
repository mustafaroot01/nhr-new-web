<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'name.required' => __('messages.validation_required'),
            'email.required' => __('messages.validation_required'),
            'email.email' => __('messages.validation_email'),
            'subject.required' => __('messages.validation_required'),
            'message.required' => __('messages.validation_required'),
        ]);

        ContactSubmission::create([
            ...$data,
            'ip_address' => $request->ip(),
            'locale' => app()->getLocale(),
        ]);

        return back()->with('success', __('messages.contact_sent'));
    }
}
