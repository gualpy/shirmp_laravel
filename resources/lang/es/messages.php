<?php

return [
    'inventory' => [
        'quantity_positive'   => 'La cantidad debe ser mayor a cero.',
        'adjustment_not_zero' => 'La cantidad de ajuste no puede ser cero.',
        'stock_negative'      => 'El stock no puede quedar negativo.',
    ],

    'cycle' => [
        'harvested_locked'     => 'Un ciclo cosechado no puede cambiar a otro estado.',
        'pond_has_active_cycle'=> 'Esta piscina ya tiene un ciclo activo.',
        'must_be_active'       => 'Esta operación solo se permite en ciclos activos.',
        'requires_stocking'    => 'El ciclo requiere una siembra antes de esta operación.',
        'final_harvest_exists' => 'Ya existe una cosecha final para este ciclo.',
    ],

    'pond' => [
        'area_ha_positive'      => 'El área de la piscina (ha) debe ser mayor a cero.',
        'not_belongs_to_cycle'  => 'La piscina seleccionada no pertenece al ciclo indicado.',
        'no_active_cycle'       => 'La piscina seleccionada no tiene un ciclo activo disponible.',
        'no_active_cycle_water' => 'La piscina seleccionada no tiene un ciclo activo disponible para registrar calidad de agua.',
    ],

    'farm' => [
        'not_found' => 'Granja no encontrada para el tenant actual.',
    ],

    'mortality' => [
        'active_cycle_only'       => 'La mortalidad diaria solo puede registrarse en un ciclo activo.',
        'recorded_at_after_start' => 'La fecha de registro debe ser igual o posterior al inicio del ciclo.',
        'recorded_at_before_end'  => 'La fecha de registro debe ser igual o anterior al cierre del ciclo.',
    ],

    'stocking' => [
        'already_exists'        => 'El ciclo ya tiene una siembra registrada.',
        'stocked_at_after_start'=> 'La fecha de siembra debe ser igual o posterior al inicio del ciclo.',
    ],

    'sampling' => [
        'sampled_at_after_stocking' => 'La fecha de muestreo debe ser igual o posterior a la fecha de siembra.',
    ],

    'harvest' => [
        'harvested_at_after_stocking' => 'La fecha de cosecha debe ser igual o posterior a la fecha de siembra.',
    ],

    'feeding' => [
        'fed_at_after_stocking' => 'La fecha de alimentación debe ser igual o posterior a la fecha de siembra.',
        'feed_type_not_found'   => 'Tipo de alimento no encontrado para el tenant actual.',
    ],

    'water_quality' => [
        'cancelled_cycle'         => 'No se puede registrar calidad de agua en un ciclo cancelado.',
        'measured_at_after_start' => 'La fecha de medición debe ser igual o posterior al inicio del ciclo.',
        'measured_at_before_end'  => 'La fecha de medición debe ser igual o anterior al cierre del ciclo.',
    ],

    'saas' => [
        'license_key_required' => 'license_key es requerido para planes onprem.',
    ],
];
