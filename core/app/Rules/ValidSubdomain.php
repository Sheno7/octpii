<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class ValidSubdomain implements ValidationRule {
    /**
     * List of reserved subdomains.
     *
     * @var array
     */
    protected $reservedSubdomains;

    /**
     * Suggested alternative subdomains.
     *
     * @var array
     */
    protected $suggestions = [];

    /**
     * Constructor.
     */
    public function __construct() {
        $this->reservedSubdomains = config('subdomains.reserved', []);
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $subdomain = $this->sanitizeSubdomain($value);

        if ($this->isSubdomainTaken($subdomain)) {
            $this->generateSuggestions($subdomain);
            $fail(__('validation.subdomain_taken', [
                'subdomain' => $subdomain,
                'suggestions' => implode(', ', $this->suggestions),
            ]));
        }
    }

    /**
     * Sanitize the input to ensure it is a valid subdomain format.
     *
     * @param  string $value
     * @return string
     */
    protected function sanitizeSubdomain(string $value): string {
        $subdomain = Str::slug($value, '-');

        // Ensure the subdomain doesn't start or end with a hyphen
        return trim($subdomain, '-');
    }

    /**
     * Check if the subdomain is taken or reserved.
     *
     * @param  string $subdomain
     * @return bool
     */
    protected function isSubdomainTaken(string $subdomain): bool {
        // Check against reserved subdomains
        if (in_array($subdomain, $this->reservedSubdomains)) {
            return true;
        }

        // Check if the subdomain exists in the database
        return Domain::where('domain', $subdomain)->exists();
    }

    /**
     * Generate suggestions for the subdomain.
     *
     * @param  string $originalSubdomain
     * @return void
     */
    protected function generateSuggestions(string $originalSubdomain): void {
        $counter = 1;

        while (count($this->suggestions) < 5) {
            $suggestedSubdomain = $originalSubdomain . '-' . $counter;

            if (!$this->isSubdomainTaken($suggestedSubdomain)) {
                $this->suggestions[] = $suggestedSubdomain;
            }

            $counter++;
        }
    }

    /**
     * Get suggestions for the subdomain.
     *
     * @return array
     */
    public function getSuggestions(): array {
        return $this->suggestions;
    }
}
