<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Worker;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

enum Position: string {
    case Developer = 'Программист';
    case DevOps = 'DevOps';
    case Administrator = 'Администратор';
    case Designer = 'Дизайнер';
}
class WorkerService
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

    /**
     * @param $data
     * @return string|void
     * @throws \Exception
     */
    public function createWorker ($data)
    {
        $mandatoryFields = ['fullname', 'email', 'phonenumber', 'position', 'birthdate'];
        foreach ($mandatoryFields as $field) {
            if (!isset($data[$field])) {
                return "field " . $field . " is undefined.";
            }
        }

        $newWorker = new Worker();
        try {
            $newWorker->setFullname($data["fullname"]);
            $newWorker->setEmail($data["email"]);
            $newWorker->setPhonenumber($data["phonenumber"]);
            $newWorker->setBirthdate(new \DateTimeImmutable($data["birthdate"]));

            if (Position::tryFrom($data["position"]) !== null)
                $newWorker->setPosition($data["position"]);
            else
                throw new \Exception("This position isn't exist.", Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            throw new \Exception('Caught exception: ' . $e->getMessage() . "\n", Response::HTTP_BAD_REQUEST);
        }
//
//        $errors = $this->validator->validate($newWorker);
//        if (count($errors) > 0) {
//            return (string)$errors;
//        } else {
            try {
                $this->entityManager->persist($newWorker);
                $this->entityManager->flush();
            } catch (\Exception $exception) {
                throw new \Exception('Caught exception: ' . $exception->getMessage() . "\n", Response::HTTP_INTERNAL_SERVER_ERROR);
            }
//        }
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