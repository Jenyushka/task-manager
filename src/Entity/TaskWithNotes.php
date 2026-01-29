<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class TaskWithNotes extends Task
{
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Notes cannot be blank for this task type')]
    private ?string $notes = null;

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getTypeName(): string
    {
        return 'Task with Notes';
    }
}
