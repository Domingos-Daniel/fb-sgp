<?php

namespace App\Filament\Resources\BeneficiarioResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BeneficiarioStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected $listeners = ['updateBeneficiarioOverview' => '$refresh'];
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
       
        $rolesCount = DB::table('beneficiarios')
        ->selectRaw(' 
            COUNT(*) as total,
            SUM(CASE WHEN tipo_beneficiario = "Individual" THEN 1 ELSE 0 END) AS individual,
            SUM(CASE WHEN tipo_beneficiario = "Institucional" THEN 1 ELSE 0 END) AS institucional
            
        ')
        ->first();
 
        return [
            Stat::make('Total', $rolesCount->total)
                ->color('warning')
                ->icon('heroicon-s-user-group')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->descriptionIcon('heroicon-m-user-group')
                ->description('Total de Beneficiários'), 
                
            Stat::make('Individual', $rolesCount->individual)   
                ->color('success')
                ->icon('heroicon-s-user')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->descriptionIcon('heroicon-m-user')
                ->description('Beneficiários Individuais'),
                
            Stat::make('Institucional', $rolesCount->institucional)
                ->color('danger')
                ->icon('heroicon-s-building-office-2')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->descriptionIcon('heroicon-m-building-office-2')
                ->description('Beneficiários Institstitucional'),

        ];
    }
}
