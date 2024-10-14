<?php

namespace App\Controller;

use App\Service\WorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class WorkerController extends AbstractController
{
    #[Route('/create-worker', name: 'create_worker', methods: ['POST'])]
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
