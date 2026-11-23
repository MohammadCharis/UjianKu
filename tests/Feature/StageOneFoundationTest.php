<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageOneFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_expected_master_data(): void
    {
        $this->seed();

        $this->assertSame(1, User::where('role', User::ADMIN)->count());
        $this->assertSame(2, User::where('role', User::GURU)->count());
        $this->assertSame(30, User::where('role', User::SISWA)->count());
        $this->assertSame(3, SchoolClass::count());
        $this->assertSame(30, Student::count());
        $this->assertSame(2, Subject::count());
        $this->assertSame(20, Question::count());
        $this->assertSame(48, QuestionOption::count());
    }

    public function test_role_middleware_allows_only_matching_role(): void
    {
        $this->seed();

        $admin = User::where('role', User::ADMIN)->firstOrFail();
        $guru = User::where('role', User::GURU)->firstOrFail();
        $siswa = User::where('role', User::SISWA)->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/guru')->assertForbidden();
        $this->actingAs($guru)->get('/guru')->assertOk();
        $this->actingAs($guru)->get('/admin')->assertForbidden();
        $this->actingAs($siswa)->get('/siswa')->assertOk();
        $this->actingAs($siswa)->get('/admin')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }
}
