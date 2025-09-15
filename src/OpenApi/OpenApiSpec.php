<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(openapi: '3.1.0')]
#[OA\Info(

    description: 'API documentation',
    title: 'My API'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Development server'
)]
class OpenApiSpec
{
    // This class exists only to hold the OpenAPI configuration
}