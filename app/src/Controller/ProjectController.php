<?php

namespace App\Controller;

use App\Service\WorkerService;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractController
{
    #[Route('/create-project', name: 'create_project', methods: ['POST'])]
    public function create (
        Request        $request,
        ProjectService $pjService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $errors = $pjService->createProject($data);

        return $this->json([
            'message' => $errors <> "" ? $errors : 'Project was created successfully!'
        ]);
    }

    #[Route('/close-project', name: 'close_project', methods: ['PUT'])]
    public function close (
        Request        $request,
        ProjectService $pjService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!(array_key_exists('name', $data) && gettype($data['name']) === 'string')) {
            return $this->json([
                'message' => "Type or name of parameter is wrong!. Parameter must have key 'name' and string type."
            ]);
        }
        $errors = $pjService->closeProject($data['name']);

        return $this->json([
            'message' => $errors <> "" ? $errors : 'Project was closed successfully!'
        ]);
    }

    #[Route('/projects/{projectId}/add_workers', name: 'add_workers', methods: ['PUT'])]
    public function addWorkers(
        int $projectId,
        Request $request,
        ProjectService $pjService,
        WorkerService $workerService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $workers = $workerService->getWorkers($data['workers']);
            $pj = $pjService->getProject($projectId);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()]);
        }

        $pjService->assignWorkers($pj, $workers);
        return $this->json([
            'message' => 'The workers was added to the project (' . $pj->getName() . ') !'
        ]);
    }

    #[Route('/project/{projectId}/remove_workers', name: 'remove_workers', methods: ['PUT'])]
    public function removeWorker(
        int $projectId,
        Request $request,
        ProjectService $pjService,
        WorkerService $workerService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $workers = $workerService->getWorkers($data['workers']);
            $pj = $pjService->getProject($projectId);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()]);
        }

        $pjService->removeWorkers($pj, $workers);
        return $this->json([
            'message' => 'The workers was removed from the project (' . $pj->getName() . ') !'
        ]);
    }
}
