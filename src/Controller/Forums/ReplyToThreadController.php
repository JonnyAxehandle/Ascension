<?php


namespace App\Controller\Forums;


use App\Entity\Forum;
use App\Entity\Post;
use App\Entity\Thread;
use App\Form\PostFormType;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/forums", name: "forums_")]
class ReplyToThreadController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[ArrayShape(['forum' => "\App\Entity\Forum", 'thread' => "\App\Entity\Thread", 'form' => "\Symfony\Component\Form\FormView"])]
    #[Route("/{forum}-{forumSlug}/{thread}-{threadSlug}/reply", name: "thread_reply")]
    #[Template("forums/reply.html.twig")]
    public function reply(Request $request, Forum $forum, string $forumSlug, Thread $thread, string $threadSlug): array|RedirectResponse
    {
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Post $post */
            $post = $form->getData();
            $thread->addPost($post);

            $this->entityManager->persist($post);
            $this->entityManager->persist($thread);
            $this->entityManager->flush();

            return $this->redirectToRoute('forums_thread_view', [
                'forum' => $forum->getId(),
                'forumSlug' => $forumSlug,
                'thread' => $thread->getId(),
                'threadSlug' => $thread->getSlug()
            ]);
        }

        return [
            'forum' => $forum,
            'thread' => $thread,
            'form' => $form->createView()
        ];
    }
}