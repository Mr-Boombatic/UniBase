<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Worker;
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
            $pj = $this->entityManager->getRepository(Project::class)->findOneBy(
                ['name' => $data['name']]);
            if ($pj <> null) {
                return "The project with such name has already been created.";
            }
        } else {
            return "'name' parameter isn't passed.";
        }

        if (!(array_key_exists('customer', $data) && gettype($data['customer']) === 'string'))
            return "'customer' parameter isn't passed.";

        $newPj->setName($data['name']);
        $newPj->setCustomer($data['customer']);

        $errors = $this->validator->validate($newPj);
        if (count($errors) > 0) {
            return (string)$errors;
        } else {
            try {
                $this->entityManager->persist($newPj);
                $this->entityManager->flush();
            } catch (\Exception $exception) {
                return 'Caught exception: ' . $exception->getMessage() . "\n";
            }
        }
    }

    public function closeProject ($name)
    {
        $pj = $this->entityManager->getRepository(Project::class)->findOneBy(
            ['name' => $name]);

        if ($pj == null)
            return "Project with such name (" . $name . ") isn't exist.";
        if ($pj->isClosed())
            return $name . " already have been closed.";

        $pj->setClosed();
        $this->entityManager->persist($pj);
        $this->entityManager->flush();
    }

    public function assignWorkers(Project $pj, array $workers)
    {
        $alreadAddedWorkers = [];
        foreach ($workers as $worker) {
            if ($pj->getWorkers()->contains($worker)) {
                array_push($alreadAddedWorkers, $worker);
                continue;
            }

            $pj->addWorker($worker);
        }

        $this->entityManager->persist($pj);
        $this->entityManager->flush();

        if (count($alreadAddedWorkers) > 0) {
            throw new \Exception("Workers have been added: " . implode(", ", $alreadAddedWorkers));
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