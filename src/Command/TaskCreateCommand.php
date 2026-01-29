<?php

namespace App\Command;

use App\Entity\TaskWithNotes;
use App\Entity\TaskWithPriority;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:create',
    description: 'Create a new task via CLI',
)]
class TaskCreateCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('Create New Task');

        // Ask for task type
        $typeQuestion = new ChoiceQuestion(
            'Select task type:',
            ['1' => 'Task with Notes', '2' => 'Task with Priority'],
            '1'
        );
        $typeQuestion->setErrorMessage('Type %s is invalid.');
        $type = $helper->ask($input, $output, $typeQuestion);

        // Ask for title
        $titleQuestion = new Question('Enter task title: ');
        $titleQuestion->setValidator(function ($answer) {
            if (empty(trim($answer))) {
                throw new \RuntimeException('Title cannot be empty');
            }
            return $answer;
        });
        $title = $helper->ask($input, $output, $titleQuestion);

        // Ask for description
        $descriptionQuestion = new Question('Enter task description: ');
        $descriptionQuestion->setValidator(function ($answer) {
            if (empty(trim($answer))) {
                throw new \RuntimeException('Description cannot be empty');
            }
            return $answer;
        });
        $description = $helper->ask($input, $output, $descriptionQuestion);

        // Create task based on type
        if ($type === 'Task with Notes' || $type === '1') {
            $task = $this->createTaskWithNotes($helper, $input, $output, $title, $description);
        } else {
            $task = $this->createTaskWithPriority($helper, $input, $output, $title, $description);
        }

        // Save task
        try {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Task "%s" created successfully! (ID: %d)',
                $task->getTitle(),
                $task->getId()
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to create task: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function createTaskWithNotes($helper, InputInterface $input, OutputInterface $output, string $title, string $description): TaskWithNotes
    {
        $notesQuestion = new Question('Enter notes for this task: ');
        $notesQuestion->setValidator(function ($answer) {
            if (empty(trim($answer))) {
                throw new \RuntimeException('Notes cannot be empty for this task type');
            }
            return $answer;
        });
        $notes = $helper->ask($input, $output, $notesQuestion);

        $task = new TaskWithNotes();
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setNotes($notes);

        return $task;
    }

    private function createTaskWithPriority($helper, InputInterface $input, OutputInterface $output, string $title, string $description): TaskWithPriority
    {
        $priorityQuestion = new ChoiceQuestion(
            'Select priority:',
            [
                TaskWithPriority::PRIORITY_LOW => 'Low',
                TaskWithPriority::PRIORITY_MEDIUM => 'Medium',
                TaskWithPriority::PRIORITY_HIGH => 'High',
                TaskWithPriority::PRIORITY_CRITICAL => 'Critical',
            ],
            TaskWithPriority::PRIORITY_MEDIUM
        );
        $priorityQuestion->setErrorMessage('Priority %s is invalid.');
        $priority = $helper->ask($input, $output, $priorityQuestion);

        $task = new TaskWithPriority();
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setPriority($priority);

        return $task;
    }
}
