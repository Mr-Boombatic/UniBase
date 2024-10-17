<?php

namespace App\Controller;

use App\Service\WorkerService;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Project;
use Symfony\Component\HttpFoundation\Response as Response;

class ProjectController extends AbstractController
{

    #[OA\Post(
            path: '/api/create-project',
            description: "Создание проекта",
            requestBody: new OA\RequestBody(
                content: new OA\JsonContent(
                    ref: new Model(type: Project::class, groups: ["create_project"]),
                    example: '{
                    "name": "project name",
                    "customer": "customer name"
                }')
            )
        ),
        OA\Response(
            response: 201,
            description: 'Project is created.'),
        OA\Response(
            response: 409,
            description: 'Project with name already exists.'),
        OA\Response(
            response: 400,
            description: 'Some fields dosn\'t exist or/and have invalid type.'),
        OA\Response(
            response: 422,
            description: 'Validation error.'),
    ]
    #[Route('/api/create-project', name: 'create_project', methods: ['POST'])]
    public function create (
        Request        $request,
        ProjectService $pjService
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $pjService->createProject($data);

            return $this->json([
                'message' => 'Project was created successfully!'
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return $this->json([
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }
    }

    #[OA\Patch(
        path: '/api/close-project',
        description: "Закрытие проекта",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Project::class, groups: ["close_project"]),
                example: '{
                    "name": "project name"
                }')
        )
    ),OA\Response(
        response: 200,
        description: 'Project is closed.',
    ),
    OA\Response(
        response: 400,
        description: 'the name of project isn\'t passed',
    ),
    OA\Response(
        response: 404,
        description: 'the project in\'t created',
    )]
    #[Route('/api/close-project', name: 'close_project',methods: ['PATCH'])]
    public function close (
        Request        $request,
        ProjectService $pjService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!(array_key_exists('name', $data) && gettype($data['name']) === 'string')) {
            return $this->json([
                'message' => "Type or name of parameter is wrong!. Parameter must have key 'name' and string type."
            ], Response::HTTP_BAD_REQUEST);
        }
        try {
            $pjService->closeProject($data['name']);

            return $this->json(['message' => 'Project was closed successfully!'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[OA\Patch(
        path: '/api/projects/{projectId}/add_workers',
        description: "Добавление пользователей в проект",
        requestBody: new OA\RequestBody(
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'workers', type: 'array', items: new OA\Items(type: 'integer')
                        ),
                    ],
                    type: 'object',
                    example: '{
                        "workers": [1, 2, 3]}'
                )
        )
    ),OA\Response(
        response: 200,
        description: 'Workers is added.',
    ), OA\Response(
        response: 400,
        description: 'Workers already added, workers with passed id don\'t exist, \'workers\' is empty or isn\'t passed.',
    ), OA\Response(
        response: 404,
        description: 'Some workers isn\'t found or project ID don\'t exist.',)]
    #[Route('/api/projects/{projectId}/add_workers', name: 'add_workers', methods: ['PATCH'])]
    public function addWorkers(
        int $projectId,
        Request $request,
        ProjectService $pjService,
        WorkerService $workerService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        return $this->changeWorkerCollection($projectId, $data, 'add', $pjService, $workerService);
    }


    #[OA\Patch(
        path: '/api/project/{projectId}/remove_workers',
        description: "Удаление пользователей из проекта",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['data'],
                properties: [
                    new OA\Property(
                        property: 'workers', type: 'array', items: new OA\Items(type: 'integer')
                    ),
                ],
                type: 'object',
                example: '{
                        "workers": [1, 2, 3]}'
            )
        )
    ),OA\Response(
        response: 200,
        description: 'Workers is removed.',
    ), OA\Response(
        response: 400,
        description: 'No workers is in specified project, workers with passed id don\'t exist, \'workers\' is empty or isn\'t passed.',
    ), OA\Response(
        response: 404,
        description: 'Some workers isn\'t found or project ID don\'t exist.',)]
    #[Route('/api/projects/{projectId}/remove_workers', name: 'remove_workers', methods: ['PATCH'])]
    public function removeWorker(
        int $projectId,
        Request $request,
        ProjectService $pjService,
        WorkerService $workerService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        return $this->changeWorkerCollection($projectId, $data, 'remove', $pjService, $workerService);
    }

    private function changeWorkerCollection(int $projectId, array $data, string $mode, ProjectService $pjSrv, WorkerService $workerSrv): JsonResponse
    {

        if (!isset($data['workers']))
            return $this->json(['message' => "Attribute 'workers' isn't specified."], Response::HTTP_BAD_REQUEST);

        if (!is_array($data['workers']) || count($data['workers']) == 0)
            return $this->json(['message' => "Json must have field 'workers' and at least one worker."],Response::HTTP_BAD_REQUEST);

        try {
            $workers = $workerSrv->getWorkers($data['workers']);
            $pj = $pjSrv->getProject($projectId);

            if ($pj === null)
                return $this->json(['message' => "Project (id: {$projectId}) is not found."], Response::HTTP_BAD_REQUEST);

            if (count($workers) === 0) {
                $nonExistentWorkers = array_diff($data['workers'], array_map(function ($worker) {
                    $worker->getId();
                }, $workers));
                return $this->json(['message' => "Some workers are not in the database. Ids: " . implode(',', $nonExistentWorkers)], Response::HTTP_NOT_FOUND);
            }

            switch ($mode) {
                case 'add':
                    $pjSrv->assignWorkers($pj, $workers);
                    break;

                case 'remove':
                    $pjSrv->removeWorkers($pj, $workers);
                    break;
            }
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], $e->getCode());
        }

        return $this->json([
            'message' => 'The workers (' . implode(", ", array_map(function ($worker) {
                    return $worker->getFullname(); }, $workers))  . ") was {$mode} to the project (" . $pj->getName() . ') !'
        ], Response::HTTP_OK);
    }
}
