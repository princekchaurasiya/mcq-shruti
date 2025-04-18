<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with('user')->paginate(10);
        return view('teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject_specialization' => ['required', 'string', 'max:255'],
            'qualifications' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($request) {
            // Create user with teacher role
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'teacher', // Set the role directly
            ]);

            // Create teacher profile
            $user->teacher()->create([
                'phone' => $request->phone,
                'subject_specialization' => $request->subject_specialization,
                'qualifications' => $request->qualifications,
                'status' => $request->status,
            ]);
        });

        return redirect()->route('teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $teacher->user_id],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject_specialization' => ['required', 'string', 'max:255'],
            'qualifications' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($request, $teacher) {
            // Update user
            $teacher->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update teacher profile
            $teacher->update([
                'phone' => $request->phone,
                'subject_specialization' => $request->subject_specialization,
                'qualifications' => $request->qualifications,
                'status' => $request->status,
            ]);
        });

        return redirect()->route('teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->user->delete(); // This will cascade delete the teacher record
        return redirect()->route('teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }
}
