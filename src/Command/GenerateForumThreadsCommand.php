<?php

namespace App\Command;

use App\Entity\Post;
use App\Entity\Thread;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate:forum-threads',
    description: 'Generates fake threads in a forum',
)]
class GenerateForumThreadsCommand extends Command
{
    private ForumRepository $forumRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ForumRepository $forumRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->forumRepository = $forumRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('forum', InputArgument::REQUIRED, 'Forum ID')
            ->addArgument('count', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $forum = $this->forumRepository->find($input->getArgument("forum"));
        if (!$forum) {
            $io->error("Forum not found");
            return Command::FAILURE;
        }

        if ($forum->isCategory()) {
            $io->error("Forum is a category");
            return Command::FAILURE;
        }

        $count = filter_var($input->getArgument("count"), FILTER_VALIDATE_INT);
        if (!$count) {
            $io->error("Invalid count");
            return Command::INVALID;
        }

        $faker = FakerFactory::create();

        for ($i = 0; $i < $count; $i++) {
            $author = $faker->userName();

            $thread = new Thread();
            $thread->setChannel($forum->getChannel());
            $thread->setAuthorName($author);
            $thread->setTitle($faker->sentence());
            $thread->setDescription($faker->sentence());

            $post = new Post();
            $post->setThread($thread);
            $post->setAuthorName($author);
            $post->setContent($faker->paragraph());

            $this->entityManager->persist($thread);
            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
