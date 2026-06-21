<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceSession;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class AttendanceWeeklyAverageChart extends ChartWidget
{
    protected static ?int $sort = 20;

    protected ?string $heading = 'Promedio de entradas y salidas por día (lun-vie)';

    protected string $color = 'primary';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '420px';

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter ??= $this->getDefaultMonth();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        $filters = [];
        $current = Carbon::parse(
            AttendanceSession::query()
                ->whereNotNull('check_in_at')
                ->whereNotNull('check_out_at')
                ->max('check_in_at') ?: now()
        )->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $month = $current->copy()->subMonths($i);
            $filters[$month->format('Y-m')] = $this->formatMonthLabel($month);
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $month = $this->filter ?: $this->getDefaultMonth();
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $sessions = $this->getClosedSessionsBetween($start, $end);

        if ($sessions->isEmpty() && $month !== $this->getDefaultMonth()) {
            $month = $this->getDefaultMonth();
            $this->filter = $month;
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $sessions = $this->getClosedSessionsBetween($start, $end);
        }

        $weekdays = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
        ];

        $checkInByDay = [];
        $checkOutByDay = [];

        foreach ($weekdays as $iso => $label) {
            $checkInByDay[$iso] = [];
            $checkOutByDay[$iso] = [];
        }

        foreach ($sessions as $session) {
            $checkIn = $session->check_in_at;
            $checkOut = $session->check_out_at;

            if (! $checkIn || ! $checkOut) {
                continue;
            }

            $weekday = $checkIn->isoWeekday();
            if ($weekday < 1 || $weekday > 5) {
                continue;
            }

            $checkInByDay[$weekday][] = ($checkIn->hour * 60) + $checkIn->minute;
            $checkOutByDay[$weekday][] = ($checkOut->hour * 60) + $checkOut->minute;
        }

        $labels = array_values($weekdays);

        $avgCheckIn = [];
        $avgCheckOut = [];

        foreach (array_keys($weekdays) as $weekday) {
            $avgCheckIn[] = $this->avgMinutesToHours($checkInByDay[$weekday]);
            $avgCheckOut[] = $this->avgMinutesToHours($checkOutByDay[$weekday]);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Entrada promedio',
                    'data' => $avgCheckIn,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Salida promedio',
                    'data' => $avgCheckOut,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|RawJs|null
     */
    protected function getOptions(): array|RawJs|null
    {
        return RawJs::make(<<<'JS'
{
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      suggestedMax: 24,
      ticks: {
        callback: function (value) {
          if (value === null || value === undefined) {
            return '';
          }
          var total = Math.round(value * 60);
          var hours = Math.floor(total / 60);
          var minutes = total % 60;
          return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        }
      },
      title: {
        display: true,
        text: 'Hora'
      }
    }
  },
  plugins: {
    tooltip: {
      callbacks: {
        label: function (context) {
          var value = context.parsed.y;
          if (value === null || value === undefined) {
            return context.dataset.label + ': —';
          }
          var total = Math.round(value * 60);
          var hours = Math.floor(total / 60);
          var minutes = total % 60;
          var time = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
          return context.dataset.label + ': ' + time;
        }
      }
    }
  }
}
JS);
    }

    /**
     * @param array<int, int> $minutes
     */
    private function avgMinutesToHours(array $minutes): ?float
    {
        if (count($minutes) === 0) {
            return null;
        }

        $avg = array_sum($minutes) / count($minutes);

        return round($avg / 60, 2);
    }

    private function getDefaultMonth(): string
    {
        $latestCheckIn = AttendanceSession::query()
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->max('check_in_at');

        if (! $latestCheckIn) {
            return now()->format('Y-m');
        }

        return Carbon::parse($latestCheckIn)->format('Y-m');
    }

    /**
     * @return Collection<int, AttendanceSession>
     */
    private function getClosedSessionsBetween(Carbon $start, Carbon $end): Collection
    {
        return AttendanceSession::query()
            ->whereBetween('check_in_at', [$start, $end])
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->get(['check_in_at', 'check_out_at']);
    }

    private function formatMonthLabel(Carbon $month): string
    {
        $labels = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $monthNumber = (int) $month->format('n');

        return $labels[$monthNumber] . ' ' . $month->format('Y');
    }
}
