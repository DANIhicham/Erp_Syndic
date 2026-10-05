<?php

namespace App\Http\Requests;

use App\Models\Appartement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransfererProprieteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appartement_id'          => 'required|exists:appartements,id',
            'nouveau_proprietaire_id' => 'required|exists:users,id',
            'date_vente'              => 'required|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'appartement_id.required'          => 'L\'appartement est obligatoire.',
            'appartement_id.exists'            => 'Appartement introuvable.',
            'nouveau_proprietaire_id.required' => 'Le nouveau propriétaire est obligatoire.',
            'nouveau_proprietaire_id.exists'   => 'Nouveau propriétaire introuvable.',
            'date_vente.required'              => 'La date de vente est obligatoire.',
            'date_vente.date'                  => 'La date de vente est invalide.',
            'date_vente.before_or_equal'       => 'La date de vente ne peut pas être dans le futur.',
        ];
    }

    /**
     * Validation métier supplémentaire après les règles Laravel.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $appartement = Appartement::find($this->appartement_id);

            if (!$appartement) {
                return;
            }

            // Règle : nouveau propriétaire différent de l'ancien
            if ((int) $this->nouveau_proprietaire_id === (int) $appartement->proprietaire_id) {
                $v->errors()->add(
                    'nouveau_proprietaire_id',
                    'Le nouveau propriétaire est identique à l\'actuel.'
                );
            }

            // Règle : l'appartement doit avoir un propriétaire actuel
            if (!$appartement->proprietaire_id) {
                $v->errors()->add(
                    'appartement_id',
                    'Cet appartement n\'a pas de propriétaire actuel à transférer.'
                );
            }
        });
    }

    /**
     * Retourner une réponse JSON en cas d'erreur (appelé depuis AJAX).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Données invalides.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}