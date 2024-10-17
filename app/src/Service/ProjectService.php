<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Worker;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\Exception;
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

    public function assignWorkers(Project $pj, array $workers)
    {
        $alreadAddedWorkers = [];
        foreach ($workers as $worker) {
            if ($pj->getWorkers()->contains($worker)) {
                array_push($alreadAddedWorkers, $worker->getId());
                continue;
            }

            $pj->addWorker($worker);
        }

        $this->entityManager->persist($pj);
        $this->entityManager->flush();

        if (count($alreadAddedWorkers) > 0) {
            throw new \Exception("Workers with following ids have been added: " . implode(", ", $alreadAddedWorkers), 409);
        }
    }

    public function getProject ($id)
    {
        $pj =  $this->entityManager->getRepository(Project::class)->findOneBy(['id' => $id]);
        if ($pj <> null)
            return $pj;
        else
            throw new \ErrorException("Project with such id does not exist.");
    }

    public function removeWorkers($pj, array $workers)
    {
            $alreadyRemovedWorkers = [];
            foreach ($workers as $worker) {
                if ($pj->getWorkers()->contains($worker)) {
                    array_push($alreadyRemovedWorkers, $worker->getFullname());
                    continue;
                }

                $pj->removeWorker($worker);
            }

            $this->entityManager->persist($pj);
            $this->entityManager->flush();

            if (count($alreadyRemovedWorkers) > 0) {
                throw new \Exception("Workers have been removed: " . implode(", ", $alreadyRemovedWorkers));
            }
    }
}