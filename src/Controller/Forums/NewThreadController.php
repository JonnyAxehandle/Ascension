<?php


namespace App\Controller\Forums;


use App\Entity\Forum;
use App\Entity\Post;
use App\Entity\Thread;
use App\Form\NewThreadFormType;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/forums", name: "forums_")]
class NewThreadController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[ArrayShape(['forum' => "\App\Entity\Forum", 'form' => "\Symfony\Component\Form\FormView"])]
    #[Route("/{forum}-{forumSlug}/newThread", name: "thread_new")]
    #[Template("forums/thread_new.html.twig")]
    public function newThread(Request $request, Forum $forum, string $forumSlug): array|RedirectResponse
    {
        // TODO: Slug validation
        $form = $this->createForm(NewThreadFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Thread $thread
             * @var Post $post
             */
            list('Thread' => $thread, 'Post' => $post) = $form->getData();
            $thread->setChannel($forum->getChannel());
            $post->setAuthorName($thread->getAuthorName());
            $post->setThread($thread);

            $this->entityManager->persist($thread);
            $this->entityManager->persist($post);
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
            'form' => $form->createView()
        ];
    }

}