<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index()
    {
        $students = Student::with('user')->latest()->paginate(10);
        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roll_number' => 'nullable|string|max:50',
            'batch' => 'nullable|string|max:50',
            'admission_date' => 'nullable|date'
        ]);

        DB::beginTransaction();
        
        try {
            // Create user account first
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'student'
            ]);
            
            // Create student profile
            $user->student()->create([
                'roll_number' => $validated['roll_number'] ?? null,
                'batch' => $validated['batch'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null
            ]);
            
            DB::commit();
            
            return redirect()->route('students.index')
                ->with('success', 'Student created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Failed to create student. Please try again.');
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load('user', 'testAttempts.mcqTest.subject');
        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $student->load('user');
        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->user->id,
            'roll_number' => 'nullable|string|max:50',
            'batch' => 'nullable|string|max:50',
            'admission_date' => 'nullable|date',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        DB::beginTransaction();
        
        try {
            // Update user account
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            
            $student->user->update($userData);
            
            // Update student profile
            $student->update([
                'roll_number' => $validated['roll_number'] ?? null,
                'batch' => $validated['batch'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null
            ]);
            
            DB::commit();
            
            return redirect()->route('students.index')
                ->with('success', 'Student updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id
            ]);
            
            return back()->withInput()->with('error', 'Failed to update student. Please try again.');
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        try {
            // Delete the user account (will cascade delete student profile)
            $student->user->delete();
            
            return redirect()->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id
            ]);
            
            return back()->with('error', 'Failed to delete student. Please try again.');
        }
    }
} 