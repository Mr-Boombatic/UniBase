<?php

namespace App\Controller;

use App\Service\WorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Worker;

class WorkerController extends AbstractController
{
    #[OA\Post(
        path: '/api/create-worker',
        description: "Создание проекта",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Worker::class, groups: ["create_worker"]),
                example: '{
                    "fullname": "project name", "phonenumber": "+3124213", "email": "test@yandex.ru","birthdate": "1900-01-01","position": "Программист" }')
        )
    ),
        OA\Response(
            response: 201,
            description: 'Worker is created.'),
        OA\Response(
            response: 400,
            description: 'Some fields dosn\'t exist or/and have invalid type.'),
        OA\Response(
            response: 422,
            description: 'Validation error.'),
    ]
    #[Route('/api/create-worker', name: 'create_worker', methods: ['POST'])]
    public function create(
        Request $request,
        WorkerService $workerService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $errors = $workerService->createWorker($data);

        return $this->json([
            'message' => $errors <> ""  ? $errors : 'New worker was created successfully!'
        ]);
    }
}
