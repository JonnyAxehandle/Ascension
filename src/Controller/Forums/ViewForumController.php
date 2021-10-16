<?php


namespace App\Controller\Forums;


use App\Entity\Forum;
use App\Entity\Thread;
use App\Repository\ThreadRepository;
use JetBrains\PhpStorm\ArrayShape;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/forums", name: "forums_")]
class ViewForumController extends AbstractController
{
    private ThreadRepository $threadRepository;
    private PaginatorInterface $paginator;

    public function __construct(ThreadRepository $threadRepository, PaginatorInterface $paginator)
    {
        $this->threadRepository = $threadRepository;
        $this->paginator = $paginator;
    }

    #[ArrayShape(['forum' => "\App\Entity\Forum", 'threads' => "\Knp\Component\Pager\Pagination\PaginationInterface", 'pinnedThreads' => "\App\Entity\Thread[]"])]
    #[Route("/{forum}-{forumSlug}", name: "forum_view")]
    #[Template("forums/forum_view.html.twig")]
    public function viewForum(Request $request, Forum $forum, string $forumSlug): array
    {
        // TODO: Slug validation
        // TODO: Sort options ( <- make that part of a search page )
        $threadQuery = $this->threadRepository->getIndexQuery($forum->getChannel());
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
}
