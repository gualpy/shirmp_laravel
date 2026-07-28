<?php

return [
    'inventory' => [
        'quantity_positive'   => 'Quantity must be greater than zero.',
        'adjustment_not_zero' => 'Adjustment quantity cannot be zero.',
        'stock_negative'      => 'Stock cannot go negative.',
    ],

    'cycle' => [
        'harvested_locked'     => 'Harvested cycle cannot be moved to another status.',
        'pond_has_active_cycle'=> 'This pond already has an active cycle.',
        'must_be_active'       => 'Only active cycles allow this operation.',
        'requires_stocking'    => 'Cycle requires stocking before this operation.',
        'final_harvest_exists' => 'A final harvest already exists for this cycle.',
    ],

    'pond' => [
        'area_ha_positive'      => 'Pond area_ha must be greater than zero.',
        'not_belongs_to_cycle'  => 'The selected pond does not belong to the provided cycle.',
        'no_active_cycle'       => 'The selected pond does not have an active cycle available.',
        'no_active_cycle_water' => 'The selected pond has no active cycle available for water quality registration.',
    ],

    'farm' => [
        'not_found' => 'Farm not found for current tenant.',
    ],

    'mortality' => [
        'active_cycle_only'       => 'Daily mortality can only be recorded for an active cycle.',
        'recorded_at_after_start' => 'recorded_at must be on or after cycle started_at.',
        'recorded_at_before_end'  => 'recorded_at must be on or before cycle ended_at.',
    ],

    'stocking' => [
        'already_exists'        => 'Cycle already has a stocking record.',
        'stocked_at_after_start'=> 'stocked_at must be on or after cycle started_at.',
    ],

    'sampling' => [
        'sampled_at_after_stocking' => 'sampled_at must be on or after stocking stocked_at.',
    ],

    'harvest' => [
        'harvested_at_after_stocking' => 'harvested_at must be on or after stocking stocked_at.',
    ],

    'feeding' => [
        'fed_at_after_stocking' => 'fed_at must be on or after stocking stocked_at.',
        'feed_type_not_found'   => 'Feed type not found for current tenant.',
    ],

    'water_quality' => [
        'cancelled_cycle'         => 'Cannot register water quality for a cancelled cycle.',
        'measured_at_after_start' => 'measured_at must be on or after cycle started_at.',
        'measured_at_before_end'  => 'measured_at must be on or before cycle ended_at.',
    ],

    'saas' => [
        'license_key_required' => 'license_key is required for onprem plans.',
    ],
];
