<?php
/**
 * Predefined cancellation reasons for booking cancellations
 * This helps categorize and analyze cancellation patterns
 */

return [
    'reasons' => [
        'schedule_conflict' => [
            'label' => 'Schedule Conflict',
            'description' => 'I have a conflicting appointment or event',
            'icon' => 'bi-calendar-x'
        ],
        'financial_issues' => [
            'label' => 'Financial Issues',
            'description' => 'Unable to afford the trip at this time',
            'icon' => 'bi-cash-stack'
        ],
        'family_emergency' => [
            'label' => 'Family Emergency',
            'description' => 'Unexpected family situation requiring attention',
            'icon' => 'bi-exclamation-triangle'
        ],
        'health_concerns' => [
            'label' => 'Health Concerns',
            'description' => 'Health issues preventing travel',
            'icon' => 'bi-heart-pulse'
        ],
        'weather_concerns' => [
            'label' => 'Weather Concerns',
            'description' => 'Concerned about weather conditions',
            'icon' => 'bi-cloud-rain'
        ],
        'found_alternative' => [
            'label' => 'Found Alternative',
            'description' => 'Found a better option or alternative service',
            'icon' => 'bi-arrow-right-circle'
        ],
        'group_size_changed' => [
            'label' => 'Group Size Changed',
            'description' => 'Number of participants changed significantly',
            'icon' => 'bi-people'
        ],
        'destination_changed' => [
            'label' => 'Destination Changed',
            'description' => 'Decided to go to a different destination',
            'icon' => 'bi-geo-alt'
        ],
        'service_concerns' => [
            'label' => 'Service Concerns',
            'description' => 'Concerns about service quality or policies',
            'icon' => 'bi-shield-exclamation'
        ],
        'other' => [
            'label' => 'Other (Please Specify)',
            'description' => 'Reason not listed above',
            'icon' => 'bi-three-dots',
            'requires_specification' => true
        ]
    ],
    
    'categories' => [
        'personal' => [
            'label' => 'Personal Reasons',
            'reasons' => ['schedule_conflict', 'family_emergency', 'health_concerns']
        ],
        'financial' => [
            'label' => 'Financial Reasons',
            'reasons' => ['financial_issues']
        ],
        'external' => [
            'label' => 'External Factors',
            'reasons' => ['weather_concerns', 'found_alternative']
        ],
        'booking_changes' => [
            'label' => 'Booking Changes',
            'reasons' => ['group_size_changed', 'destination_changed']
        ],
        'service_related' => [
            'label' => 'Service Related',
            'reasons' => ['service_concerns']
        ],
        'other' => [
            'label' => 'Other',
            'reasons' => ['other']
        ]
    ]
];
