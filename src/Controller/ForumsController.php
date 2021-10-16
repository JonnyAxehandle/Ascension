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
use Doctrine\ORM\Query;
use JetBrains\PhpStorm\ArrayShape;
use Knp\Component\Pager\PaginatorInterface;
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
    private PaginatorInterface $paginator;

    /**
     * ForumsController constructor.
     * @param ForumRepository $forumRepository
     * @param ThreadRepository $threadRepository
     * @param PostRepository $postRepository
     */
    public function __construct(ForumRepository $forumRepository,
                                ThreadRepository $threadRepository,
                                PostRepository $postRepository,
                                EntityManagerInterface $entityManager,
                                PaginatorInterface $paginator)
    {
        $this->forumRepository = $forumRepository;
        $this->threadRepository = $threadRepository;
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
    }

    #[ArrayShape(['categories' => "\App\Entity\Forum[]"])]
    #[Route("/", name: "index")]
    #[Template("forums/index.html.twig")]
    public function index(): array
    {
        // SELECT u FROM User u JOIN u.address a WHERE a.city = 'Berlin'
        $query = $this->entityManager->createQuery(<<<DQL
            SELECT category , forums , channel , lastThread , lastPost
            FROM \App\Entity\Forum category
            JOIN category.Children forums
            JOIN forums.Channel channel
            LEFT JOIN channel.Threads lastThread
            WITH lastThread = FIRST(
                SELECT thread
                FROM \App\Entity\Thread thread
                WHERE thread.Channel = channel
                ORDER BY thread.id DESC
            )
            LEFT JOIN lastThread.Posts lastPost
            WITH lastPost = FIRST(
                SELECT post
                FROM \App\Entity\Post post
                WHERE post.Thread = lastThread
                ORDER BY post.id DESC
            )
            WHERE category.Parent IS NULL
        DQL);

        // TODO: Filter by user view permissions
        return [
            'categories' => $query->execute()
        ];
    }

    #[ArrayShape(['forum' => "\App\Entity\Forum", 'threads' => "\Knp\Component\Pager\Pagination\PaginationInterface", 'pinnedThreads' => "\App\Entity\Thread[]"])]
    #[Route("/{forum}-{forumSlug}", name: "forum_view")]
    #[Template("forums/forum_view.html.twig")]
    public function viewForum(Request $request, Forum $forum, string $forumSlug): array
    {
        // TODO: Slug validation
        // TODO: Sort options
        $threadQuery = $this->threadRepository->createQueryBuilder("t")
            ->where("t.Channel = :Channel")
            ->setParameter("Channel", $forum->getChannel())
            ->orderBy('t.LastPostDate', 'DESC');
        $threads = $this->paginator->paginate($threadQuery, $request->query->getInt('page', 1), 10);

        // TODO: Pinned threads
        /** @var Thread[] $pinnedThreads */
        $pinnedThreads = [];

        return [
            'forum' => $forum,
            'threads' => $threads,
            'pinnedThreads' => $pinnedThreads
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
    public function viewThread(Request $request, Forum $forum, string $forumSlug, Thread $thread, string $threadSlug): array
    {
        $postQuery = $this->postRepository->createQueryBuilder('p')
            ->where("p.Thread = :Thread")
            ->setParameter("Thread", $thread)
            ->orderBy("p.DatePosted", "ASC");

        $posts = $this->paginator->paginate($postQuery, $request->query->getInt('page', 1), 10);

        // TODO: Slug validation
        return [
            'forum' => $forum,
            'thread' => $thread,
            'posts' => $posts
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
