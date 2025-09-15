<?php
namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GreetingResponse',
    title: 'Greeting Response',
    description: 'Successful greeting response',
    required: ['message', 'status'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Hello, world!'),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
    ]
)]
class GreetingResponse {}