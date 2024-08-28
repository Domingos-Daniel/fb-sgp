<?php

namespace App\Imports;

use App\Models\Beneficiario;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class BeneficiariosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Beneficiario([
            'nome' => $row['nome'],
            'tipo_beneficiario' => $row['tipo_de_beneficiario'],
            'bi' => $row['bi'],
            'nif' => $row['nif'],
            'data_nascimento' => $row['data_de_nascimento'],
            'genero' => $row['genero'],
            'email' => $row['email'],
            'telemovel' => $row['telemovel'],
            'telemovel_alternativo' => $row['telemovel_alternativo'],
            'endereco' => $row['endereco'],
            'pais' => $row['pais'],
            'provincia' => $row['provincia'],
            'ano_frequencia' => $row['ano_de_frequencia'],
            'curso' => $row['curso'],
            'universidade_ou_escola' => $row['universidade_ou_escola'],
            'coordenadas_bancarias' => $row['coordenadas_bancarias'],
            'id_criador' => Auth::id(), // Atribui o ID do usuário autenticado como criador
        ]);
    }
}
