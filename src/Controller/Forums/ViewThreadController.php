<?php


namespace App\Controller\Forums;


use App\Entity\Forum;
use App\Entity\Thread;
use App\Repository\PostRepository;
use JetBrains\PhpStorm\ArrayShape;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/forums", name: "forums_")]
class ViewThreadController extends AbstractController
{
    private PostRepository $postRepository;
    private PaginatorInterface $paginator;

    /**
     * ViewThreadController constructor.
     * @param PostRepository $postRepository
     */
    public function __construct(PostRepository $postRepository, PaginatorInterface $paginator)
    {
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
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
}