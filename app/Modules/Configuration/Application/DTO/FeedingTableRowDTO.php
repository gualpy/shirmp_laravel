<?php

namespace App\Modules\Configuration\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class FeedingTableRowDTO extends BaseDTO
{
    public function __construct(
        public readonly int $dayFrom,
        public readonly int $dayTo,
        public readonly ?float $ppFromGrams,
        public readonly ?float $ppToGrams,
        public readonly float $feedPct,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dayFrom: (int) $data['day_from'],
            dayTo: (int) $data['day_to'],
            ppFromGrams: isset($data['pp_from_grams']) ? (float) $data['pp_from_grams'] : null,
            ppToGrams: isset($data['pp_to_grams']) ? (float) $data['pp_to_grams'] : null,
            feedPct: (float) $data['feed_pct'],
        );
    }

    public function toArray(): array
    {
        return [
            'day_from' => $this->dayFrom,
            'day_to' => $this->dayTo,
            'pp_from_grams' => $this->ppFromGrams,
            'pp_to_grams' => $this->ppToGrams,
            'feed_pct' => $this->feedPct,
        ];
    }
}
