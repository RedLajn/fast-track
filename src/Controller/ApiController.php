<?php
namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Hello, world!'),
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
            ]
        )
    )]
    #[OA\Parameter(
        name: 'name',
        in: 'query',
        description: 'Optional name for personalized greeting',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'format',
        in: 'query',
        description: 'Response format',
        schema: new OA\Schema(type: 'string', default: 'json')
    )]
    #[OA\Tag(name: 'greetings')]
    public function hello(): JsonResponse
    {
        $name = $this->getRequest()->query->get('name');
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
                type: 'object',
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Hello, world!'),
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
                ]
            )
        )
    )]
    #[OA\Parameter(
        name: 'count',
        in: 'query',
        description: 'Number of greetings to return',
        schema: new OA\Schema(type: 'integer', default: 3, minimum: 1, maximum: 10)
    )]
    #[OA\Tag(name: 'greetings')]
    public function greetings(): JsonResponse
    {
        $count = $this->getRequest()->query->getInt('count', 3);
        $count = max(1, min(10, $count)); // Clamp between 1-10

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

    private function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}