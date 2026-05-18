<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', $this->route('depense')->type);

        $rules = [
            'titre'       => ['required', 'string', 'max:255'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'categorie'   => ['required', 'string', Rule::in([
                'maintenance', 'securite', 'travaux', 'energie', 'nettoyage', 'admin',
            ])],
            'description' => ['nullable', 'string'],
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['nullable', 'date', 'after_or_equal:date_debut'],
            'is_active'   => ['boolean'],
        ];

        if ($type === 'variable') {
            $rules['montant'] = ['nullable', 'numeric', 'min:0'];
        } else {
            $rules['montant'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }
}