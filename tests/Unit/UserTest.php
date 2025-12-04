<?php

namespace Tests\Unit;

use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_loans()
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->loans->contains($loan));
        $this->assertInstanceOf(Loan::class, $user->loans->first());
    }

    public function test_user_has_many_reservations()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->reservations->contains($reservation));
        $this->assertInstanceOf(Reservation::class, $user->reservations->first());
    }

    public function test_user_is_active_by_default()
    {
        $user = User::factory()->create();
        $this->assertEquals('active', $user->status);
    }
}
