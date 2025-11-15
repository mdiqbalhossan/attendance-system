<?php

namespace App\Http\Requests\Students;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'student_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_id')->ignore(request()->route('student')),
            ],
            'class' => ['required', 'string', 'max:10'],
            'section' => ['required', 'string', 'max:10'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Student name is required.',
            'student_id.required' => 'Student ID is required.',
            'student_id.unique' => 'This student ID is already taken.',
            'class.required' => 'Class is required.',
            'section.required' => 'Section is required.',
            'photo.image' => 'Photo must be an image file.',
            'photo.mimes' => 'Photo must be a file of type: jpeg, png, jpg, gif.',
            'photo.max' => 'Photo size must not exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student ID',
        ];
    }
}
