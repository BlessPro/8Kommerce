<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()?->id)],
            'business_name' => ['required', 'string', 'max:160'],
            'phone_country' => ['nullable', 'string', Rule::in(['+233'])],
            'phone_number' => ['required', 'string', 'max:20'],
            'payout_method' => ['nullable', 'string', Rule::in(['MOMO', 'BANK'])],
            'settlement_bank_code' => ['nullable', 'string', 'max:20', 'required_with:account_number,account_name'],
            'account_number' => ['nullable', 'string', 'max:20', 'required_with:settlement_bank_code,account_name'],
            'account_name' => ['nullable', 'string', 'max:160', 'required_with:settlement_bank_code,account_number'],
            // New PINs are four digits, but legacy six-digit PINs must still
            // be accepted here so existing sellers can confirm payout edits.
            'current_pin' => ['nullable', 'digits_between:4,6'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
            'fulfillment_options' => ['nullable', 'array', 'max:20'],
            'fulfillment_options.*.type' => ['required', 'string', Rule::in(['pickup', 'delivery'])],
            'fulfillment_options.*.label' => ['required', 'string', 'max:160'],
            'fulfillment_options.*.fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'fulfillment_options.*.details' => ['nullable', 'string', 'max:180'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $phoneNumber = $this->input('phone_number');
            $country = $this->input('phone_country', '+233');

            if (! $phoneNumber) {
                return;
            }

            $normalized = Phone::normalize($phoneNumber, $country);
            if (! $normalized) {
                $validator->errors()->add('phone_number', 'Phone number must contain digits.');

                return;
            }

            $digits = preg_replace('/\D+/', '', $normalized);
            if ($country === '+233' && strlen($digits) !== 12) {
                $validator->errors()->add('phone_number', 'Phone number must be 9 digits after removing the leading 0.');
            }

            $userId = $this->user()?->id;
            if ($normalized && User::where('phone', $normalized)->where('id', '!=', $userId)->exists()) {
                $validator->errors()->add('phone_number', 'This WhatsApp number is already linked to another account.');
            }

            $profile = $this->user()?->sellerProfile;
            $payoutFields = ['payout_method', 'settlement_bank_code', 'account_number', 'account_name'];
            $payoutChanged = collect($payoutFields)->contains(function (string $field) use ($profile): bool {
                $submitted = (string) $this->input($field, '');

                return $profile
                    ? $submitted !== (string) ($profile->{$field} ?? '')
                    : $submitted !== '';
            });

            if ($payoutChanged && (! $this->filled('current_pin') || ! Hash::check((string) $this->input('current_pin'), (string) $this->user()?->pin_hash))) {
                $validator->errors()->add('current_pin', 'Enter your current PIN to change payout details.');
            }
        });
    }
}
