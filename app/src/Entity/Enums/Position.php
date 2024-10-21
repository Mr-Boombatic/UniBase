<?php

namespace App\Entity\Enums;

enum Position: string
{
    case Developer = 'Программист';
    case DevOps = 'DevOps';
    case Administrator = 'Администратор';
    case Designer = 'Дизайнер';
}