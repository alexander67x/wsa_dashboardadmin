<?php

namespace Database\Seeders;

use App\Models\AttendanceEvent;
use App\Models\AttendanceSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->with('empleado')
            ->orderBy('id')
            ->limit(5)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        foreach ($users as $index => $user) {
            $baseDate = $now->copy()->subDays($index + 1)->startOfDay();
            $checkInAt = $baseDate->copy()->setTime(8, 15, 0);
            $checkOutAt = $baseDate->copy()->setTime(17, 30, 0);

            $this->createSession(
                $user,
                $checkInAt,
                $checkOutAt,
                ['label' => 'Obra Central', 'lat' => -12.046374, 'lng' => -77.042793],
                ['label' => 'Obra Central', 'lat' => -12.046500, 'lng' => -77.043100]
            );

            if ($index === 0) {
                $openCheckIn = $now->copy()->startOfDay()->setTime(8, 5, 0);
                $this->createSession(
                    $user,
                    $openCheckIn,
                    null,
                    ['label' => 'Obra Norte', 'lat' => -12.050100, 'lng' => -77.040900],
                    null
                );
            }
        }
    }

    private function createSession(User $user, Carbon $checkInAt, ?Carbon $checkOutAt, array $startLocation, ?array $endLocation): void
    {
        $checkInEvent = AttendanceEvent::create([
            'user_id' => $user->id,
            'type' => 'check_in',
            'occurred_at' => $checkInAt,
            'latitude' => $startLocation['lat'] ?? null,
            'longitude' => $startLocation['lng'] ?? null,
            'location_label' => $startLocation['label'] ?? null,
        ]);

        $session = AttendanceSession::create([
            'user_id' => $user->id,
            'check_in_event_id' => $checkInEvent->id,
            'check_in_at' => $checkInAt,
            'check_in_lat' => $checkInEvent->latitude,
            'check_in_lng' => $checkInEvent->longitude,
            'check_in_location_label' => $checkInEvent->location_label,
            'status' => 'open',
        ]);

        if (! $checkOutAt) {
            return;
        }

        $checkOutEvent = AttendanceEvent::create([
            'user_id' => $user->id,
            'type' => 'check_out',
            'occurred_at' => $checkOutAt,
            'latitude' => $endLocation['lat'] ?? null,
            'longitude' => $endLocation['lng'] ?? null,
            'location_label' => $endLocation['label'] ?? null,
        ]);

        $hoursWorked = round($checkInAt->diffInMinutes($checkOutAt) / 60, 2);

        $session->fill([
            'check_out_event_id' => $checkOutEvent->id,
            'check_out_at' => $checkOutAt,
            'check_out_lat' => $checkOutEvent->latitude,
            'check_out_lng' => $checkOutEvent->longitude,
            'check_out_location_label' => $checkOutEvent->location_label,
            'hours_worked' => $hoursWorked,
            'status' => 'closed',
        ]);

        $session->save();
    }
}
