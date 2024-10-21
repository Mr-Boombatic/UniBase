<?php

namespace App\Service;

use App\Entity\Worker;
use Symfony\Component\HttpFoundation\Response as Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkerService
{
    private EntityManagerInterface $entityManager;

    public function __construct (
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function createWorker($data): void
    {
        $mandatoryFields = ['fullname', 'email', 'phonenumber', 'position', 'birthdate'];
        $undefinedFields = [];
        foreach ($mandatoryFields as $field) {
            if (!isset($data[$field])) {
                array_push($undefinedFields, $field);
            }
        }

        if (count($undefinedFields) <> 0)
            throw new \Exception("Fields (" .implode(', ', $undefinedFields). ") are undefined", Response::HTTP_BAD_REQUEST);

        $newWorker = new Worker();
        try {
            $newWorker->setFullname($data["fullname"]);
            $newWorker->setEmail($data["email"]);
            $newWorker->setPhonenumber($data["phonenumber"]);
            $newWorker->setBirthdate(new \DateTimeImmutable($data["birthdate"]));

            if (\App\Entity\Enums\Position::tryFrom($data["position"]) !== null)
                $newWorker->setPosition($data["position"]);
            else
                throw new \Exception("This position isn't exist.", Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            throw new \Exception('Caught exception: ' . $e->getMessage() . "\n", Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->entityManager->persist($newWorker);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw new \Exception('Caught exception: ' . $exception->getMessage() . "\n", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array $ids
     * @return Worker[]
     * @throws Exception
     */
    public function getWorkers (array $ids): array
    {
        $workers = $this->entityManager->getRepository(Worker::class)->findBy(array('id' => $ids));

        if (count($ids) != count($workers)) {
            $nonExistentWorkers = array_diff($ids, array_map(function ($worker) {
                    return $worker->getId();
                }, $workers));
            throw new Exception("Some workers are not in the database. Ids: " . implode(',', $nonExistentWorkers), Response::HTTP_NOT_FOUND);
        }

        return $workers;
    }
}