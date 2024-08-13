<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\User;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $columns;

    public function __construct($columns)
    {
        $this->columns = $columns;
    }

    public function collection()
    {
        return User::select($this->columns)->get();
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function map($user): array
    {
        return array_map(function($column) use ($user) {
            return $user->{$column};
        }, $this->columns);
    }
}
