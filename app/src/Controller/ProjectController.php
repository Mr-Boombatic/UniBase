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
        if (!isset($data['workers']) && !is_array($data['workers'] && count($data['workers']) > 0)) {
            return $this->json(['message' => "Json must have field 'workers' and at least one worker."], );
        }

        try {
            $workers = $workerService->getWorkers($data['workers']);
            $pj = $pjService->getProject($projectId);

            $pjService->assignWorkers($pj, $workers);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()]);
        }

        return $this->json([
            'message' => 'The workers (' . implode(", ", array_map(function ($worker) {
                    return $worker->getFullname(); }, $workers))  . ' was added to the project (' . $pj->getName() . ') !'
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
        if (!isset($data['workers']) && !is_array($data['workers'] && count($data['workers']) > 0)) {
            return $this->json(['message' => "Json must have field 'workers' and at least one worker."], );
        }

        try {
            $workers = $workerService->getWorkers($data['workers']);
            $pj = $pjService->getProject($projectId);

            $pjService->removeWorkers($pj, $workers);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()]);
        }

        return $this->json([
            'message' => 'The workers (' . implode(", ", array_map(function ($worker) {
                    return $worker->getFullname(); }, $workers))  . ' was removed from the project (' . $pj->getName() . ') !'
        ]);
    }
}
