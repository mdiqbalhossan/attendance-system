<?php

namespace App\Http\Controllers;

use App\Http\Requests\Students\StoreStudentRequest;
use App\Http\Requests\Students\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Student::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by class
        if ($request->filled('class')) {
            $query->byClass($request->class);
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->bySection($request->section);
        }

        $students = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Get unique classes and sections for filters (only non-null values)
        $classes = Student::distinct()
            ->whereNotNull('class')
            ->pluck('class')
            ->sort()
            ->values();
        
        $sections = Student::distinct()
            ->whereNotNull('section')
            ->pluck('section')
            ->sort()
            ->values();

        return Inertia::render('students/Index', [
            'students' => StudentResource::collection($students),
            'filters' => [
                'search' => $request->search ?? '',
                'class' => $request->class ?? '',
                'section' => $request->section ?? '',
            ],
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('students/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle photo upload
        if (request()->hasFile('photo')) {
            $validated['photo'] = request()->file('photo')->store('students', 'public');
        }

        Student::create($validated);

        return to_route('students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): Response
    {
        return Inertia::render('students/Show', [
            'student' => new StudentResource($student),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): Response
    {
        return Inertia::render('students/Edit', [
            'student' => new StudentResource($student),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();

        // Handle photo upload
        if (request()->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            $validated['photo'] = request()->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        return to_route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        // Delete photo if exists
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return to_route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
