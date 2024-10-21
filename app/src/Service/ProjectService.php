<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Worker;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;

    public function __construct (
        ValidatorInterface     $validator,
        EntityManagerInterface $entityManager
    )
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    public function createProject ($data)
    {
        $newPj = new Project();

        if (array_key_exists('name', $data) && gettype($data['name']) === 'string') {
            $pj = $this->entityManager->getRepository(Project::class)->findOneBy(['name' => $data['name']]);

            if ($pj <> null) {
                throw new \InvalidArgumentException("The project with such name has already been created.", 409);
            }
        } else {
            throw new \InvalidArgumentException("'name' parameter isn't passed.", 400);
        }

        if (!(array_key_exists('customer', $data) && gettype($data['customer']) === 'string'))
            throw new \Exception("'customer' parameter isn't passed.", 400);

        $newPj->setName($data['name']);
        $newPj->setCustomer($data['customer']);

        // валидация оказалась не нужна
        $validationErrors = $this->validator->validate($newPj);
        if (count($validationErrors) > 0) {
            throw new \Exception('Validation exceptions: ' . (string)$validationErrors . "\n", 422);
        } else {
            $this->entityManager->persist($newPj);
            $this->entityManager->flush();
        }
    }

    public function closeProject ($name)
    {
        $pj = $this->entityManager->getRepository(Project::class)->findOneBy(
            ['name' => $name]);

        if ($pj == null)
            throw new Exception("Project with such name (" . $name . ") isn't exist.", 404);
        if ($pj->isClosed())
            throw new Exception($name . " already have been closed.", 400);

        $pj->setClosed();
        $this->entityManager->persist($pj);
        $this->entityManager->flush();
    }

    /**
     * @param Project $pj
     * @param array $workers
     * @return void
     * @throws \Exception
     */
    public function assignWorkers(Project $pj, array $workers)
    {
        $alreadyAddedWorkers = [];
        $workersInProject = $pj->getWorkers();
        foreach ($workers as $worker) {
            if ($workersInProject->contains($worker)) {
                array_push($alreadyAddedWorkers, $worker->getId());
                continue;
            }

            $pj->addWorker($worker);
        }

        $this->entityManager->persist($pj);
        $this->entityManager->flush();

        if (count($alreadyAddedWorkers) > 0) {
            throw new \Exception("Workers with following ids already have been added: " . implode(", ", $alreadyAddedWorkers), 409);
        }
    }

    /**
     * @param int $id
     * @throws \InvalidArgumentException
     * @return Project
     */
    public function getProject (int $id): Project
    {
        $pj =  $this->entityManager->getRepository(Project::class)->findOneBy(['id' => $id]);
        if ($pj == null) {
            throw new \InvalidArgumentException("Project (id: {$id}) is not found.", Response::HTTP_BAD_REQUEST);
        }

        return $pj;
    }

    /**
     * @param Project $pj
     * @param array $workers
     * @return void
     * @throws \Exception
     */
    public function removeWorkers(Project $pj, array $workers): void
    {
        $outsidePj = [];
        foreach ($workers as $worker) {
            if (!$pj->getWorkers()->contains($worker)) {
                array_push($outsidePj, $worker->getFullname());
                continue;
            }

            $pj->removeWorker($worker);
        }

        $this->entityManager->persist($pj);
        $this->entityManager->flush();

        if (count($outsidePj) > 0) {
            throw new \Exception("These workers are not in the project anyway: " . implode(", ", $outsidePj), 400);
        }
    }
}