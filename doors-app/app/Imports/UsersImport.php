<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'name'       => $row['full_name'] ?? null,
            'npk'        => $row['npk'] ?? null,
            'division'   => $row['division'] ?? null,
            'department' => $row['department'] ?? null,
            'position'   => $row['position'] ?? null,
            'email'      => $row['email'] ?? null,
            'phone'      => $row['phone'] ?? null,
            'password'   => Hash::make('password'), // Default password, consider a more secure approach
        ]);
    }
}
