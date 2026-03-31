<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $memberQuery = Member::when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('member_id', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->grade, function ($query, $grade) {
                $query->where('grade', 'like', "%{$grade}%");
            });

        $members = (clone $memberQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $typeCounts = (clone $memberQuery)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $memberTotals = [
            'students' => (int) ($typeCounts['student'] ?? 0),
            'teachers' => (int) ($typeCounts['teacher'] ?? 0),
            'staff' => (int) ($typeCounts['staff'] ?? 0),
        ];

        return Inertia::render('Members/Index', [
            'members' => $members,
            'memberTotals' => $memberTotals,
            'filters' => $request->only(['search', 'type', 'grade'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|string|unique:members,member_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|string|min:8',
            'type' => 'required|in:student,teacher,staff',
            'grade' => 'nullable|string',
            'contact_number' => 'nullable|string',
        ]);

        Member::create($validated);

        return redirect()->back()->with('success', 'Member added successfully!');
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'member_id' => 'required|string|unique:members,member_id,' . $member->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'password' => 'nullable|string|min:8',
            'type' => 'required|in:student,teacher,staff',
            'grade' => 'nullable|string',
            'contact_number' => 'nullable|string',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $member->update($validated);

        return redirect()->back()->with('success', 'Member updated successfully!');
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->back()->with('success', 'Member deleted successfully!');
    }
}
