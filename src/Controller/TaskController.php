<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskWithNotes;
use App\Entity\TaskWithPriority;
use App\Form\TaskWithNotesType;
use App\Form\TaskWithPriorityType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAllOrderedByCreationTime(),
        ]);
    }

    #[Route('/new', name: 'task_new', methods: ['GET'])]
    public function selectType(): Response
    {
        return $this->render('task/select_type.html.twig');
    }

    #[Route('/new/with-notes', name: 'task_new_with_notes', methods: ['GET', 'POST'])]
    public function newWithNotes(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new TaskWithNotes();
        $form = $this->createForm(TaskWithNotesType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Task with notes created successfully!');

            return $this->redirectToRoute('task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
            'task_type' => 'Task with Notes'
        ]);
    }

    #[Route('/new/with-priority', name: 'task_new_with_priority', methods: ['GET', 'POST'])]
    public function newWithPriority(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new TaskWithPriority();
        $form = $this->createForm(TaskWithPriorityType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Task with priority created successfully!');

            return $this->redirectToRoute('task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
            'task_type' => 'Task with Priority'
        ]);
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/complete', name: 'task_complete', methods: ['POST'])]
    public function complete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('complete'.$task->getId(), $request->request->get('_token'))) {
            $task->markAsCompleted();
            $entityManager->flush();

            $this->addFlash('success', 'Task marked as completed!');
        }

        return $this->redirectToRoute('task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/reopen', name: 'task_reopen', methods: ['POST'])]
    public function reopen(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('reopen'.$task->getId(), $request->request->get('_token'))) {
            $task->markAsOpen();
            $entityManager->flush();

            $this->addFlash('success', 'Task reopened!');
        }

        return $this->redirectToRoute('task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', 'Task deleted successfully!');
        }

        return $this->redirectToRoute('task_index', [], Response::HTTP_SEE_OTHER);
    }
}
