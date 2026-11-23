<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $class = SchoolClass::where('name', trim((string) $row['kelas']))->firstOrFail();
                $email = Str::lower(trim((string) $row['email']));

                $user = User::firstOrNew(['email' => $email]);
                $user->name = trim((string) $row['nama']);

                if (! $user->exists) {
                    $user->password = 'password';
                    $user->role = User::SISWA;
                    $user->email_verified_at = now();
                }

                $user->save();

                Student::updateOrCreate(
                    ['nis' => trim((string) $row['nis'])],
                    ['user_id' => $user->id, 'class_id' => $class->id],
                );
            }
        });
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nis' => ['required', 'max:50'],
            'kelas' => ['required', 'string', 'max:100', 'exists:classes,name'],
        ];
    }
}
