<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
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
            'date' => ['required', 'date', 'before_or_equal:today'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'attendances.*.status' => ['required', 'in:Present,Absent,Late'],
            'attendances.*.note' => ['nullable', 'string', 'max:500'],
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
            'date.required' => 'Please select a date.',
            'date.before_or_equal' => 'Attendance date cannot be in the future.',
            'attendances.required' => 'Please provide attendance records.',
            'attendances.min' => 'At least one attendance record is required.',
            'attendances.*.student_id.required' => 'Student ID is required for each record.',
            'attendances.*.student_id.exists' => 'One or more students do not exist.',
            'attendances.*.status.required' => 'Status is required for each record.',
            'attendances.*.status.in' => 'Invalid attendance status provided.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Add date to each attendance record
        if ($this->has('attendances') && $this->has('date')) {
            $attendances = collect($this->attendances)->map(function ($attendance) {
                return array_merge($attendance, ['date' => $this->date]);
            })->all();

            $this->merge(['attendances' => $attendances]);
        }
    }
}
