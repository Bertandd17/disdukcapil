<?php

namespace App\Rules;

use App\Services\SecureFileUploadService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * FileValidationRule — Laravel validation rule untuk file upload security.
 *
 * Usage:
 *   $request->validate([
 *       'ktp_image' => ['required', 'file', new FileValidationRule()],
 *   ]);
 *
 * Atau dengan custom error message:
 *   $request->validate([
 *       'ktp_image' => ['required', 'file', new FileValidationRule('File KTP')],
 *   ]);
 */
class FileValidationRule implements ValidationRule
{
    private string $fieldLabel;

    public function __construct(string $fieldLabel = 'File')
    {
        $this->fieldLabel = $fieldLabel;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! ($value instanceof UploadedFile)) {
            $fail("{$this->fieldLabel} harus berupa file yang valid.");
            return;
        }

        $service = app(SecureFileUploadService::class);
        $result = $service->validateFile($value);

        if (! $result->isValid()) {
            $firstError = $result->firstError();
            if ($firstError !== null) {
                $fail($firstError);
            } else {
                $fail("{$this->fieldLabel} tidak valid.");
            }
        }
    }
}
