<?php

namespace App\Controller;

    use App\Entity\Task;
    use App\Form\TaskType;
    use App\Repository\TaskRepository;
    use Doctrine\ORM\Exception\ORMException;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        try {
            $tasks = $taskRepository->findBy([], ['createdAt' => 'DESC']);

            return $this->render('task/index.html.twig', [
                'tasks' => $tasks,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to load tasks: ' . $e->getMessage());
            return $this->render('task/index.html.twig', ['tasks' => []]);
        }
    }

    #[Route('/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $taskRepository->save($task, true);
                $this->addFlash('success', 'Task created successfully!');
                return $this->redirectToRoute('task_index');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Failed to create task. Please try again.');
                // Log the actual error
                error_log('Task creation failed: ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'An unexpected error occurred. Please try again.');
                error_log('Unexpected error in task creation: ' . $e->getMessage());
            }
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        try {
            return $this->render('task/show.html.twig', [
                'task' => $task,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to load task details.');
            return $this->redirectToRoute('task_index');
        }
    }

    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $taskRepository->save($task, true);
                $this->addFlash('success', 'Task updated successfully!');
                return $this->redirectToRoute('task_index');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Failed to update task. Please try again.');
                error_log('Task update failed: ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'An unexpected error occurred. Please try again.');
                error_log('Unexpected error in task update: ' . $e->getMessage());
            }
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            try {
                $taskRepository->remove($task, true);
                $this->addFlash('success', 'Task deleted successfully!');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Failed to delete task. Please try again.');
                error_log('Task deletion failed: ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'An unexpected error occurred. Please try again.');
                error_log('Unexpected error in task deletion: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid security token. Please try again.');
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/{id}/toggle-status', name: 'task_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        if (!$this->isCsrfTokenValid('toggle' . $task->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('task_index');
        }

        try {
            $newStatus = match ($task->getStatus()) {
                'pending' => 'in_progress',
                'in_progress' => 'completed',
                'completed' => 'pending',
                default => 'pending'
            };

            $task->setStatus($newStatus);
            $taskRepository->save($task, true);
            $this->addFlash('success', 'Task status updated to: ' . str_replace('_', ' ', $newStatus));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to update task status.');
            error_log('Status toggle failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('task_index');
    }

}
