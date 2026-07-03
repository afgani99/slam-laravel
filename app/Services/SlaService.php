<?php

namespace App\Services;

use App\Models\Cid;
use App\Models\Ticket;
use Carbon\CarbonInterface;

class SlaService
{
    /**
     * Hitung effective downtime (menit) untuk satu ticket.
     * Hanya ticket closed dengan case_type = Link Down.
     * Clamp ke 0 jika hasil negatif.
     * Pending interval tanpa ended_at tidak dihitung.
     */
    public function calculateEffectiveDowntime(Ticket $ticket): int
    {
        if (! $ticket->countsForSla()) {
            return 0;
        }

        $durationMinutes = $ticket->started_at->diffInMinutes($ticket->finished_at);

        $totalPendingMinutes = $ticket->pendingIntervals()
            ->whereNotNull('ended_at')
            ->get()
            ->sum(fn ($interval) => $interval->started_at->diffInMinutes($interval->ended_at));

        $effective = $durationMinutes - $totalPendingMinutes;

        return max(0, $effective);
    }

    /**
     * Hitung SLA untuk rentang waktu tertentu.
     * Hanya ticket closed + Link Down.
     */
    public function calculateSlaForPeriod(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $totalMinutesInPeriod = $startDate->diffInMinutes($endDate);

        $cids = Cid::with(['tickets' => function ($query) use ($startDate, $endDate) {
            $query->where('status', Ticket::STATUS_CLOSED)
                ->where('case_type', Ticket::CASE_LINK_DOWN)
                ->whereBetween('started_at', [$startDate, $endDate]);
        }])->get();

        $results = [];

        foreach ($cids as $cid) {
            if ($cid->tickets->isEmpty()) {
                continue;
            }

            $totalDuration = 0;
            $totalPending = 0;

            foreach ($cid->tickets as $ticket) {
                if (! $ticket->finished_at) {
                    continue;
                }

                $duration = (int) $ticket->started_at->diffInMinutes($ticket->finished_at);
                $totalDuration += $duration;

                $pendingMinutes = (int) $ticket->pendingIntervals()
                    ->whereNotNull('ended_at')
                    ->get()
                    ->sum(fn ($interval) => (int) $interval->started_at->diffInMinutes($interval->ended_at));

                $totalPending += $pendingMinutes;
            }

            $effectiveDowntime = max(0, $totalDuration - $totalPending);
            $slaAchieved = $totalMinutesInPeriod > 0
                ? round((($totalMinutesInPeriod - $effectiveDowntime) / $totalMinutesInPeriod) * 100, 2)
                : 100.00;

            $status = $slaAchieved >= (float) $cid->sla_percentage ? 'Aman' : 'Perlu Restitusi';

            $results[] = [
                'id' => $cid->id,
                'cid' => $cid->cid,
                'vendor_name' => $cid->vendor_name,
                'customer_name' => $cid->customer_name,
                'service' => $cid->service,
                'sla_target' => (float) $cid->sla_percentage,
                'total_downtime' => $totalDuration,
                'total_pending' => $totalPending,
                'effective_downtime' => $effectiveDowntime,
                'sla_achieved' => $slaAchieved,
                'status' => $status,
            ];
        }

        return $results;
    }

    /**
     * Hitung SLA untuk rentang waktu tertentu (semua case_type).
     * Hanya ticket closed, tanpa filter case_type.
     */
    public function calculateSlaForPeriodAllTickets(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $totalMinutesInPeriod = $startDate->diffInMinutes($endDate);

        $cids = Cid::with(['tickets' => function ($query) use ($startDate, $endDate) {
            $query->where('status', Ticket::STATUS_CLOSED)
                ->whereBetween('started_at', [$startDate, $endDate]);
        }])->get();

        $results = [];

        foreach ($cids as $cid) {
            if ($cid->tickets->isEmpty()) {
                continue;
            }

            $totalDuration = 0;
            $totalPending = 0;

            foreach ($cid->tickets as $ticket) {
                if (! $ticket->finished_at) {
                    continue;
                }

                $duration = (int) $ticket->started_at->diffInMinutes($ticket->finished_at);
                $totalDuration += $duration;

                $pendingMinutes = (int) $ticket->pendingIntervals()
                    ->whereNotNull('ended_at')
                    ->get()
                    ->sum(fn ($interval) => (int) $interval->started_at->diffInMinutes($interval->ended_at));

                $totalPending += $pendingMinutes;
            }

            $effectiveDowntime = max(0, $totalDuration - $totalPending);
            $slaAchieved = $totalMinutesInPeriod > 0
                ? round((($totalMinutesInPeriod - $effectiveDowntime) / $totalMinutesInPeriod) * 100, 2)
                : 100.00;

            $status = $slaAchieved >= (float) $cid->sla_percentage ? 'Aman' : 'Perlu Restitusi';

            $results[] = [
                'id' => $cid->id,
                'cid' => $cid->cid,
                'vendor_name' => $cid->vendor_name,
                'customer_name' => $cid->customer_name,
                'service' => $cid->service,
                'sla_target' => (float) $cid->sla_percentage,
                'total_downtime' => $totalDuration,
                'total_pending' => $totalPending,
                'effective_downtime' => $effectiveDowntime,
                'sla_achieved' => $slaAchieved,
                'status' => $status,
            ];
        }

        return $results;
    }

    /**
     * Dapatkan daftar CID yang perlu restitusi pada rentang waktu tertentu.
     */
    public function getRestitutionCidsForPeriod(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        return array_values(
            array_filter(
                $this->calculateSlaForPeriod($startDate, $endDate),
                fn ($item) => $item['status'] === 'Perlu Restitusi'
            )
        );
    }

    /**
     * Statistik untuk dashboard.
     */
    public function getDashboardStats(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $openedCount = Ticket::where('status', Ticket::STATUS_OPEN)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $pendingCount = Ticket::where('status', Ticket::STATUS_PENDING)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $closedCount = Ticket::where('status', Ticket::STATUS_CLOSED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $restitutionCount = count($this->getRestitutionCidsForPeriod($startDate, $endDate));

        return [
            'opened_count' => $openedCount,
            'pending_count' => $pendingCount,
            'closed_count' => $closedCount,
            'restitution_count' => $restitutionCount,
        ];
    }
}
