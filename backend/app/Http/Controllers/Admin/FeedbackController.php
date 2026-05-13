<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = Feedback::with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $feedbacks = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => Feedback::count(),
            'open'        => Feedback::where('status', 'open')->count(),
            'in_progress' => Feedback::where('status', 'in_progress')->count(),
            'resolved'    => Feedback::where('status', 'resolved')->count(),
            'closed'      => Feedback::where('status', 'closed')->count(),
            'bugs'        => Feedback::where('category', 'bug')->count(),
        ];

        return view('admin.feedbacks.index', compact('feedbacks', 'stats'));
    }

    public function show(Feedback $feedback): View
    {
        $feedback->load('user', 'prediction');
        return view('admin.feedbacks.show', compact('feedback'));
    }

    public function respond(Request $request, Feedback $feedback): RedirectResponse
    {
        $request->validate([
            'admin_response' => 'required|string|max:2000',
            'status'         => 'required|in:in_progress,resolved,closed',
        ]);

        $feedback->update([
            'admin_response' => $request->admin_response,
            'status'         => $request->status,
            'resolved_at'    => in_array($request->status, ['resolved', 'closed']) ? now() : $feedback->resolved_at,
        ]);

        return back()->with('success', 'Réponse enregistrée.');
    }

    public function updateStatus(Request $request, Feedback $feedback): RedirectResponse
    {
        $request->validate(['status' => 'required|in:open,in_progress,resolved,closed']);
        $feedback->update(['status' => $request->status]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();
        return redirect()->route('admin.feedbacks.index')->with('success', 'Feedback supprimé.');
    }
}
