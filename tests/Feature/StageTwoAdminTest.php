<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StageTwoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_master_data_and_roles_are_enforced(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.classes.store'), ['name' => 'X IPA 3', 'grade' => '10'])
            ->assertRedirect(route('admin.classes.index'));

        $this->assertDatabaseHas('classes', ['name' => 'X IPA 3', 'grade' => '10']);

        $this->actingAs($admin)
            ->post(route('admin.classes.store'), ['name' => 'X IPA 3', 'grade' => '10'])
            ->assertSessionHasErrors('name');

        $class = SchoolClass::where('name', 'X IPA 3')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'name' => 'Siswa Baru',
                'email' => 'siswa-baru@ujianku.test',
                'nis' => '20249991',
                'class_id' => $class->id,
            ])
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', ['email' => 'siswa-baru@ujianku.test', 'role' => User::SISWA]);
        $this->assertDatabaseHas('students', ['nis' => '20249991', 'class_id' => $class->id]);

        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_admin_can_update_and_delete_student(): void
    {
        $admin = User::factory()->admin()->create();
        $class = SchoolClass::factory()->create();
        $newClass = SchoolClass::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        $this->actingAs($admin)
            ->put(route('admin.students.update', $student), [
                'name' => 'Nama Baru',
                'email' => $student->user->email,
                'nis' => '20249993',
                'class_id' => $newClass->id,
            ])
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', ['id' => $student->id, 'nis' => '20249993', 'class_id' => $newClass->id]);
        $this->assertDatabaseHas('users', ['id' => $student->user_id, 'name' => 'Nama Baru']);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $student->user_id]);
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $class = SchoolClass::factory()->create(['name' => 'X IPA 1']);
        $csv = "nama,email,nis,kelas\nSiswa Impor,impor@ujianku.test,20249992,X IPA 1\n";

        $this->actingAs($admin)
            ->post(route('admin.students.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', ['email' => 'impor@ujianku.test', 'role' => User::SISWA]);
        $this->assertDatabaseHas('students', ['nis' => '20249992', 'class_id' => $class->id]);
    }
}
