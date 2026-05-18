<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayerDepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant'        => ['required', 'numeric', 'min:0.01'],
            'date_paiement'  => ['required', 'date', 'before_or_equal:today'],
            'mode_paiement'  => ['required', Rule::in(['virement', 'cheque', 'especes', 'carte'])],
            'reference'      => ['nullable', 'string', 'max:100'],
            'commentaire'    => ['nullable', 'string', 'max:1000'],
            'document'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'montant.required'       => 'Le montant est obligatoire.',
            'montant.min'            => 'Le montant doit être supérieur à 0.',
            'date_paiement.required' => 'La date de paiement est obligatoire.',
            'date_paiement.before_or_equal' => 'La date de paiement ne peut pas être dans le futur.',
            'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
            'document.mimes'         => 'Le justificatif doit être un PDF, JPG ou PNG.',
            'document.max'           => 'Le fichier ne doit pas dépasser 10 Mo.',
        ];
    }
}