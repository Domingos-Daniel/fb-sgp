<?php

namespace App\Imports;

use App\Models\Beneficiario;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BeneficiariosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Registre os cabeçalhos para debugging sem interromper o fluxo
        Log::info('Cabeçalhos do Excel:', array_keys($row));
        Log::info('Dados da linha:', $row);
        
        // Verifica se existem diferentes possibilidades para os nomes das colunas
        $nome = $row['nome'] ?? $row['nome_completo'] ?? $row['nomecompleto'] ?? $row['beneficiary'] ?? null;
        $iban = $row['iban'] ?? $row['coordenadas_bancarias'] ?? null;
        $pais = $row['pais'] ?? $row['country'] ?? null;
        $endereco = $row['endereco'] ?? $row['city'] ?? null;
        
        // Se nome for nulo, não crie o registro
        if ($nome === null) {
            Log::warning('Linha ignorada: nome é null', $row);
            return null;
        }
        
        $data = [
            'nome' => $nome,
            'coordenadas_bancarias' => $iban,
            'id_criador' => Auth::id(),
        ];
        
        // Adiciona campos opcionais se disponíveis
        if ($pais !== null) {
            $data['pais'] = $pais;
        }
        
        if ($endereco !== null) {
            $data['endereco'] = $endereco;
        }
        
        return new Beneficiario($data);
    }
}
