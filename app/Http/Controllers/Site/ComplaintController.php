<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Complaint');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:10000'],
            'name' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'incident_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $complaint = Complaint::create([
            'subject' => $data['subject'],
            'details' => $data['details'],
            'name' => $data['name'] ?? null,
            'contact' => $data['contact'] ?? null,
            'incident_location' => $data['incident_location'] ?? null,
            'incident_date' => $data['incident_date'] ?? null,
            'is_anonymous' => empty($data['name']),
        ]);

        return back()->with('success', __('messages.complaint_sent') . ' ' . $complaint->reference);
    }
}
