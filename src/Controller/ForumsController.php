<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Post;
use App\Entity\Thread;
use App\Form\NewThreadFormType;
use App\Form\PostFormType;
use App\Repository\ForumRepository;
use App\Repository\PostRepository;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/forums", name: "forums_")]
class ForumsController extends AbstractController
{

    /**
     * @var ForumRepository Repository for accessing forums
     */
    private ForumRepository $forumRepository;

    /**
     * @var ThreadRepository Repository for accessing threads
     */
    private ThreadRepository $threadRepository;

    /**
     * @var PostRepository
     */
    private PostRepository $postRepository;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * ForumsController constructor.
     * @param ForumRepository $forumRepository
     * @param ThreadRepository $threadRepository
     * @param PostRepository $postRepository
     */
    public function __construct(ForumRepository $forumRepository,
                                ThreadRepository $threadRepository,
                                PostRepository $postRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->forumRepository = $forumRepository;
        $this->threadRepository = $threadRepository;
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
    }

    #[ArrayShape(['categories' => "\App\Entity\Forum[]"])]
    #[Route("/", name: "index")]
    #[Template("forums/index.html.twig")]
    public function index(): array
    {
        // TODO: Filter by user view permissions
        return [
            'categories' => $this->forumRepository->findCategories()
        ];
    }

    #[ArrayShape(['forum' => "\App\Entity\Forum", 'threads' => "\App\Entity\Thread[]"])]
    #[Route("/{forum}-{forumSlug}", name: "forum_view")]
    #[Template("forums/forum_view.html.twig")]
    public function viewForum(Request $request, Forum $forum, string $forumSlug): array
    {
        // TODO: Thread pagination
        // TODO: Slug validation
        return [
            'forum' => $forum,
            'threads' => $this->threadRepository->findBy([
                'Channel' => $forum->getChannel()
            ])
        ];
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

    #[ArrayShape(['forum' => "\App\Entity\Forum", 'thread' => "\App\Entity\Thread", 'posts' => "\App\Entity\Post[]"])]
    #[Route("/{forum}-{forumSlug}/{thread}-{threadSlug}", name: "thread_view")]
    #[Template("forums/thread_view.html.twig")]
    public function viewThread(Forum $forum, string $forumSlug, Thread $thread, string $threadSlug): array
    {
        // TODO: Post pagination
        // TODO: Slug validation
        return [
            'forum' => $forum,
            'thread' => $thread,
            'posts' => $this->postRepository->findBy([
                'Thread' => $thread
            ])
        ];
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
            $post->setThread($thread);

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
            'thread' => $thread,
            'form' => $form->createView()
        ];
    }
}
