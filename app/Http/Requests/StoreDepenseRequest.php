<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sécurisez selon vos gates / policies
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'residence_id' => ['required', 'exists:residences,id'],
            'titre'        => ['required', 'string', 'max:255'],
            'fournisseur'  => ['nullable', 'string', 'max:255'],
            'categorie'    => ['required', 'string', Rule::in([
                'maintenance', 'securite', 'travaux', 'energie', 'nettoyage', 'admin',
            ])],
            'type' => ['required', Rule::in(['mensuel', 'trimestriel', 'unique', 'variable'])],
            'description'  => ['nullable', 'string'],
            'date_debut'   => ['required', 'date'],
            'date_fin'     => ['nullable', 'date', 'after_or_equal:date_debut'],
            'is_active'    => ['boolean'],
        ];

        // Montant obligatoire sauf pour "variable"
        if ($type === 'variable') {
            $rules['montant'] = ['nullable', 'numeric', 'min:0'];
        } else {
            $rules['montant'] = ['required', 'numeric', 'min:0'];
        }

        // Pour mensuel : date_fin ne peut dépasser date_debut + 1 an
        if ($type === 'mensuel') {
            $rules['date_fin'][] = function ($attribute, $value, $fail) {
                if ($value) {
                    $debut = \Carbon\Carbon::parse($this->input('date_debut'));
                    $fin   = \Carbon\Carbon::parse($value);
                    if ($fin->diffInMonths($debut) > 12) {
                        $fail('Pour une charge mensuelle, la période ne peut pas dépasser 12 mois.');
                    }
                }
            };
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'titre.required'         => 'Le titre de la dépense est obligatoire.',
            'categorie.required'     => 'Veuillez choisir une catégorie.',
            'type.required'          => 'Le type de dépense est obligatoire.',
            'montant.required'       => 'Le montant est obligatoire pour ce type de dépense.',
            'date_debut.required'    => 'La date de début est obligatoire.',
            'date_fin.after_or_equal'=> 'La date de fin doit être égale ou postérieure à la date de début.',
        ];
    }
}