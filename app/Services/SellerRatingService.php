<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFeedback;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SellerRatingService
{
    public function summary(int $userId): array
    {
        return $this->summaries([$userId])->get($userId, $this->emptySummary());
    }

    public function summaries(array $userIds): Collection
    {
        $rows = OrderFeedback::query()
            ->join('orders', 'orders.id', '=', 'order_feedbacks.order_id')
            ->whereIn('orders.user_id', $userIds)
            ->where('order_feedbacks.type', OrderFeedback::TYPE_RECEIVED)
            ->whereNotNull('order_feedbacks.rating')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->select('orders.user_id', DB::raw('COUNT(order_feedbacks.id) as review_count'), DB::raw('AVG(order_feedbacks.rating) as average_rating'))
            ->groupBy('orders.user_id')
            ->get()
            ->keyBy('user_id')
            ->map(fn ($row) => [
                'average' => round((float) $row->average_rating, 1),
                'count' => (int) $row->review_count,
                'public' => (int) $row->review_count >= 3,
            ]);

        return collect($userIds)->mapWithKeys(fn ($id) => [(int) $id => $rows->get($id, $this->emptySummary())]);
    }

    private function emptySummary(): array
    {
        return ['average' => null, 'count' => 0, 'public' => false];
    }
}
