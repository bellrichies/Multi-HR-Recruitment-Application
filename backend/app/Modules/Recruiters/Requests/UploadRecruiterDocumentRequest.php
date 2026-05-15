<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Requests;

use App\Core\Request;
use App\Core\ValidationException;
use App\Core\Validator;

class UploadRecruiterDocumentRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
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
}
