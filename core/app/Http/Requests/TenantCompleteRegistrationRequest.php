<?php

namespace App\Http\Requests;

use App\Rules\ValidSubdomain;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class TenantCompleteRegistrationRequest extends FormRequest {

    protected $validSubdomainRule;

    public function __construct() {
        parent::__construct();
        $this->validSubdomainRule = new ValidSubdomain();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            // 'vendor.plan_id' => ['exists:plans,id'],
            'vendor.sectors' => ['required', 'array', 'min:1'],
            'vendor.sectors.*' => ['exists:sectors,id'],
            'vendor.org_name_en' => ['required', 'string', 'max:50'],
            'vendor.org_name_ar' => ['required', 'string', 'max:50'],
            'domain.domain' => ['nullable', 'string', $this->validSubdomainRule, 'max:30', 'alpha_dash'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation() {
        if (empty($this->input('domain.domain'))) {
            $this->merge([
                'domain' => [
                    'domain' => $this->generateUniqueSubdomain($this->input('vendor.org_name_en'))
                ],
            ]);
        }
    }

    /**
     * Generate a unique subdomain from the organization name.
     *
     * @param  string|null  $orgName
     * @return string
     */
    protected function generateUniqueSubdomain(?string $orgName): string {
        // Generate base subdomain from the organization name
        $baseSubdomain = Str::slug($orgName ?? 'default', '-');

        // Trim and truncate to 30 characters
        $baseSubdomain = Str::limit(trim($baseSubdomain, '-'), 30, '');

        // Start with the base subdomain
        $uniqueSubdomain = $baseSubdomain;
        $counter = 1;

        // Ensure uniqueness in the database
        while ($this->isSubdomainTaken($uniqueSubdomain)) {
            $uniqueSubdomain = Str::limit($baseSubdomain, 28, '') . '-' . $counter; // Reserve space for counter
            $counter++;
        }

        return $uniqueSubdomain;
    }

    /**
     * Check if a subdomain is taken or reserved.
     *
     * @param  string  $subdomain
     * @return bool
     */
    protected function isSubdomainTaken(string $subdomain): bool {
        $reservedSubdomains = config('subdomains.reserved', []);

        // Check against reserved subdomains
        if (in_array($subdomain, $reservedSubdomains)) {
            return true;
        }

        // Check if subdomain exists in the database
        return Domain::where('domain', $subdomain)->exists();
    }


    /**
     * Customize the response on validation failure.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void {
        $errors = $validator->errors();

        // Include suggestions in the response
        $errors->add('suggestions', $this->validSubdomainRule->getSuggestions());

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors->messages(),
        ], 422));
    }
}
