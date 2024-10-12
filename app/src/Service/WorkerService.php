<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Worker;
use Doctrine\DBAL\Exception\ConstraintViolationException;
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
                return "This position isn't exist.";
        } catch (\Exception $e) {
            return 'Caught exception: ' . $e->getMessage() . "\n";
        }

        $errors = $this->validator->validate($newWorker);
        if (count($errors) > 0) {
            return (string)$errors;
        } else {
            try {
                $this->entityManager->persist($newWorker);
                $this->entityManager->flush();
            } catch (\Exception $exception) {
                return 'Caught exception: ' . $exception->getMessage() . "\n";
            }
        }
    }

    public function getWorkers ($ids): array
    {
        $workers = $this->entityManager->getRepository(Worker::class)->findBy(array('id' => $ids));
        return $workers;
    }
}