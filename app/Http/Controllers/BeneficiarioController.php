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

        return $pdf->download("beneficiario_{$beneficiario->id}.pdf");
    }
}
