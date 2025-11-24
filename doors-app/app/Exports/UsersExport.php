<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $users = User::select('name', 'npk', 'division', 'department', 'position', 'email', 'phone')->get();

        return $users;
    }

    public function headings(): array
    {
        return [
            'FULL NAME',
            'NPK',
            'DIVISION',
            'DEPARTMENT',
            'POSITION',
            'EMAIL',
            'PHONE',
        ];
    }
}
