<?php
namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * Returns a greeting message.
     *
     * This endpoint provides a simple greeting message.
     */
    #[Route('/api/hello', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response with greeting message',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Hello, world!'),
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
            ],
            type: 'object'
        )
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'Optional name for personalized greeting',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'format',
        description: 'Response format',
        in: 'query',
        schema: new OA\Schema(type: 'string', default: 'json')
    )]
    #[OA\Tag(name: 'greetings')]
    public function hello(Request $request): JsonResponse
    {
        $name = $request->query->get('name');
        $message = $name ? sprintf('Hello, %s!', $name) : 'Hello, world!';

        return $this->json([
            'message' => $message,
            'status' => 'success',
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM)
        ]);
    }

    /**
     * Returns multiple greeting messages.
     *
     * This endpoint provides an array of greeting messages.
     */
    #[Route('/api/greetings', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response with multiple greetings',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Hello, world!'),
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Parameter(
        name: 'count',
        description: 'Number of greetings to return',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 3, maximum: 10, minimum: 1)
    )]
    #[OA\Tag(name: 'greetings')]
    public function greetings(Request $request): JsonResponse
    {
        $count = $request->query->getInt('count', 3);
        $count = max(1, min(10, $count));

        $greetings = [];
        for ($i = 1; $i <= $count; $i++) {
            $greetings[] = [
                'message' => sprintf('Greeting message %d', $i),
                'status' => 'success',
                'timestamp' => (new \DateTime())->format(\DateTime::ATOM)
            ];
        }

        return $this->json($greetings);
    }
}