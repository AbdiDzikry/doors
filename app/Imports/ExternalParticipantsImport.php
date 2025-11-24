<?php

namespace App\Imports;

use App\Models\ExternalParticipant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExternalParticipantsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ExternalParticipant([
            'name'       => $row['name'],
            'email'      => $row['email'],
            'phone'      => $row['phone'],
            'company'    => $row['company'],
            'department' => $row['department'],
            'address'    => $row['address'],
            'type'       => 'external',
        ]);
    }
}
