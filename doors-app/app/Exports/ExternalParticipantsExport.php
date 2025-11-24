<?php

namespace App\Exports;

use App\Models\ExternalParticipant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExternalParticipantsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ExternalParticipant::select('name', 'email', 'phone', 'company', 'department', 'address')->get();
    }

    public function headings(): array
    {
        return [
            'NAME',
            'EMAIL',
            'PHONE',
            'COMPANY',
            'DEPARTMENT',
            'ADDRESS',
        ];
    }
}
