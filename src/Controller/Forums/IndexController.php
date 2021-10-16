<?php


namespace App\Controller\Forums;


use App\Repository\ForumRepository;
use JetBrains\PhpStorm\ArrayShape;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/forums", name: "forums_")]
class IndexController extends AbstractController
{
    private ForumRepository $forumRepository;

    public function __construct(ForumRepository $forumRepository)
    {
        $this->forumRepository = $forumRepository;
    }

    #[ArrayShape(['categories' => "\App\Entity\Forum[]"])]
    #[Route("/", name: "index")]
    #[Template("forums/index.html.twig")]
    public function index(): array
    {
        // TODO: Filter by user view permissions
        $categories = $this->forumRepository->getIndex();

        return [
            'categories' => $categories
        ];
    }
}