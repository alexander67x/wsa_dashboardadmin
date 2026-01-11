<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use App\Models\AttendanceSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Lista el historial de jornadas del usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = AttendanceSession::query()
            ->where('user_id', $user->id)
            ->orderByDesc('check_in_at');

        if ($request->filled('from')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $query->where('check_in_at', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $query->where('check_in_at', '<=', $to);
        }

        return $query->get()
            ->map(fn (AttendanceSession $session) => $this->serializeSession($session))
            ->values();
    }

    /**
     * Registra un check-in o check-out.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:check_in,check_out',
            'occurred_at' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_label' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $occurredAt = Carbon::parse($data['occurred_at'] ?? now());

        return DB::transaction(function () use ($data, $user, $occurredAt) {
            if ($data['type'] === 'check_in') {
                $openSession = AttendanceSession::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'open')
                    ->first();

                if ($openSession) {
                    return response()->json([
                        'message' => 'Ya existe una jornada en curso.'
                    ], 409);
                }

                $event = AttendanceEvent::create([
                    'user_id' => $user->id,
                    'type' => 'check_in',
                    'occurred_at' => $occurredAt,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'location_label' => $data['location_label'] ?? null,
                ]);

                $session = AttendanceSession::create([
                    'user_id' => $user->id,
                    'check_in_event_id' => $event->id,
                    'check_in_at' => $event->occurred_at,
                    'check_in_lat' => $event->latitude,
                    'check_in_lng' => $event->longitude,
                    'check_in_location_label' => $event->location_label,
                    'status' => 'open',
                ]);

                return response()->json([
                    'session' => $this->serializeSession($session),
                ], 201);
            }

            $session = AttendanceSession::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->orderByDesc('check_in_at')
                ->first();

            if (! $session) {
                return response()->json([
                    'message' => 'No hay una jornada abierta para finalizar.'
                ], 409);
            }

            $event = AttendanceEvent::create([
                'user_id' => $user->id,
                'type' => 'check_out',
                'occurred_at' => $occurredAt,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'location_label' => $data['location_label'] ?? null,
            ]);

            $hoursWorked = $this->calculateHours($session->check_in_at, $event->occurred_at);

            $session->fill([
                'check_out_event_id' => $event->id,
                'check_out_at' => $event->occurred_at,
                'check_out_lat' => $event->latitude,
                'check_out_lng' => $event->longitude,
                'check_out_location_label' => $event->location_label,
                'hours_worked' => $hoursWorked,
                'status' => 'closed',
            ]);
            $session->save();

            return response()->json([
                'session' => $this->serializeSession($session),
            ]);
        });
    }

    /**
     * Muestra el detalle de una jornada específica.
     */
    public function show(Request $request, int|string $id)
    {
        $user = $request->user();
        $session = AttendanceSession::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return $this->serializeSession($session);
    }

    private function calculateHours(?Carbon $checkIn, ?Carbon $checkOut): ?float
    {
        if (! $checkIn || ! $checkOut) {
            return null;
        }

        $minutes = $checkIn->diffInMinutes($checkOut);

        return round($minutes / 60, 2);
    }

    private function serializeSession(AttendanceSession $session): array
    {
        $checkInAt = $session->check_in_at;
        $checkOutAt = $session->check_out_at;
        $location = $session->check_out_location_label ?: $session->check_in_location_label;

        $startCoords = $this->coordsOrNull($session->check_in_lat, $session->check_in_lng);
        $endCoords = $this->coordsOrNull($session->check_out_lat, $session->check_out_lng);

        return [
            'id' => (string) $session->id,
            'date' => $checkInAt?->toDateString(),
            'checkIn' => $checkInAt?->format('H:i'),
            'checkOut' => $checkOutAt?->format('H:i'),
            'location' => $location,
            'hoursWorked' => $session->hours_worked,
            'startLocationLabel' => $session->check_in_location_label,
            'endLocationLabel' => $session->check_out_location_label,
            'startCoords' => $startCoords,
            'endCoords' => $endCoords,
            'apiId' => (string) $session->id,
            'status' => $session->status,
            'checkInAt' => $checkInAt?->toDateTimeString(),
            'checkOutAt' => $checkOutAt?->toDateTimeString(),
        ];
    }

    private function coordsOrNull(?float $latitude, ?float $longitude): ?array
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }
}
