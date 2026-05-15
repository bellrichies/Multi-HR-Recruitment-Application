<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Requests;

use App\Core\Request;
use App\Core\ValidationException;
use App\Core\Validator;

class JobSeekerDetailRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function skill(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'skill_name' => 'required|string|max:120',
            'proficiency_level' => 'nullable|string|max:80',
        ]);
    }

    public function workExperience(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'company_name' => 'required|string|max:180',
            'job_title' => 'required|string|max:180',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_current' => 'nullable|integer',
            'description' => 'nullable|string|max:2000',
        ]);
    }

    public function education(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'institution' => 'required|string|max:180',
            'qualification' => 'required|string|max:150',
            'field_of_study' => 'nullable|string|max:150',
            'start_year' => 'nullable|integer',
            'end_year' => 'nullable|integer',
        ]);
    }

    public function certification(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'name' => 'required|string|max:180',
            'issuer' => 'nullable|string|max:180',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        $file = $request->files('file');

        if (is_array($file)) {
            $data['file'] = $file;
        }

        return $data;
    }

    public function document(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'document_type' => 'required|string|max:100',
        ]);

        $file = $request->files('file');

        if (! is_array($file)) {
            throw new ValidationException(['file' => ['Document file is required.']]);
        }

        $data['file'] = $file;

        return $data;
    }

    public function guarantor(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'full_name' => 'required|string|max:180',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'relationship' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:1000',
            'occupation' => 'nullable|string|max:150',
        ]);

        $file = $request->files('file');

        if (is_array($file)) {
            $data['file'] = $file;
        }

        return $data;
    }

    public function reviewDocument(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'status' => 'required|string|in:approved,rejected',
        ]);
    }
}
