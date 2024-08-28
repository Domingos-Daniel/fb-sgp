<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use Barryvdh\DomPDF\Facade\Pdf;

class BeneficiarioController extends Controller
{
    public function exportPdf($id)
    {
        $beneficiario = Beneficiario::findOrFail($id);

        $pdf = Pdf::loadView('beneficiarios.pdf', compact('beneficiario'));
        $datetime = date('d-m-Y_H-i-s');
        return $pdf->download("beneficiario_{$beneficiario->nome}_{$datetime}.pdf");
    }
}
