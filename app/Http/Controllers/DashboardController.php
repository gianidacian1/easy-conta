<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Dashboard;
use App\Models\Balance;
use App\Models\ZDocument;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get dashboard widgets for the user
        $query = Dashboard::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('position');

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $dashboardWidgets = $query->paginate($request->get('per_page', 25))
            ->withQueryString();

        // Get summary statistics
        $stats = $this->getDashboardStats($user);

        return Inertia::render('dashboard/Index', [
            'dashboardWidgets' => $dashboardWidgets,
            'stats' => $stats,
            'filters' => $request->only(['search', 'per_page'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'widget_type' => 'required|string|in:chart,table,metric,text',
            'widget_data' => 'nullable|array',
            'position' => 'nullable|integer',
            'size' => 'nullable|string|in:small,medium,large',
        ]);

        $dashboard = Dashboard::create([
            ...$validated,
            'user_id' => auth()->id(),
            'is_active' => true,
            'position' => $validated['position'] ?? Dashboard::where('user_id', auth()->id())->max('position') + 1
        ]);

        return redirect()->back()->with('success', 'Dashboard widget created successfully');
    }

    public function update(Request $request, Dashboard $dashboard)
    {
        // Ensure user owns this dashboard widget
        if ($dashboard->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'widget_type' => 'required|string|in:chart,table,metric,text',
            'widget_data' => 'nullable|array',
            'position' => 'nullable|integer',
            'size' => 'nullable|string|in:small,medium,large',
            'is_active' => 'nullable|boolean'
        ]);

        $dashboard->update($validated);

        return redirect()->back()->with('success', 'Dashboard widget updated successfully');
    }

    public function destroy(Dashboard $dashboard)
    {
        // Ensure user owns this dashboard widget
        if ($dashboard->user_id !== auth()->id()) {
            abort(403);
        }

        $dashboard->delete();

        return redirect()->back()->with('success', 'Dashboard widget deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        Dashboard::whereIn('id', $ids)
            ->where('user_id', auth()->user()->id)
            ->delete();

        return redirect()->back()->with('success', 'Dashboard widgets deleted successfully');
    }

    private function getDashboardStats($user)
    {
        return [
            'total_balances' => Balance::where('user_id', $user->id)->count(),
            'total_z_documents' => ZDocument::where('user_id', $user->id)->count(),
            'total_dashboard_widgets' => Dashboard::where('user_id', $user->id)->where('is_active', true)->count(),
            'recent_balances' => Balance::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(['id', 'cont', 'denumirea_contului', 'created_at']),
            'recent_z_documents' => ZDocument::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(['id', 'number', 'sales', 'final_balance', 'created_at'])
        ];
    }
}