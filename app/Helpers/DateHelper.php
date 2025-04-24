<?php

namespace App\Helpers;

use App\Models\Pagamento;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class DateHelper
{
    /**
     * Converte qualquer formato de data para um objeto Carbon
     * Lida com formatos brasileiros (dd/mm/yyyy) e ISO (yyyy-mm-dd)
     */
    public static function parseAnyDate($date)
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if (empty($date)) {
            return now();
        }

        try {
            // Abordagem direta para formato brasileiro (dd/mm/yyyy)
            if (is_string($date) && strpos($date, '/') !== false) {
                // Log para debug
                Log::debug('Convertendo data brasileira', ['data' => $date]);
                
                $parts = explode('/', $date);
                if (count($parts) == 3) {
                    $day = (int)$parts[0];
                    $month = (int)$parts[1];
                    $year = (int)$parts[2];
                    
                    // Se tem hora também
                    if (strpos($parts[2], ' ') !== false) {
                        list($year, $time) = explode(' ', $parts[2], 2);
                        $year = (int)$year;
                        
                        if (strpos($time, ':') !== false) {
                            list($hour, $minute) = explode(':', $time, 2);
                            return Carbon::create($year, $month, $day, (int)$hour, (int)$minute, 0);
                        }
                    }
                    
                    // Apenas data (sem hora)
                    return Carbon::create($year, $month, $day, 0, 0, 0);
                }
            }

            // Se já está em formato ISO ou outro reconhecido pelo Carbon
            return Carbon::parse($date);
            
        } catch (Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao converter data', [
                'data' => $date, 
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback seguro
            return now();
        }
    }
}
