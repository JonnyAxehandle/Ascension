<?php

namespace App\Command;

use App\Entity\Post;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate:posts',
    description: 'Add a short description for your command',
)]
class GeneratePostsCommand extends Command
{
    private ThreadRepository $threadRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(ThreadRepository $threadRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->threadRepository = $threadRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('thread', InputArgument::REQUIRED, 'Thread ID')
            ->addArgument('count', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $thread = $this->threadRepository->find($input->getArgument("thread"));
        if (!$thread) {
            $io->error("Thread not found");
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

            $post = new Post();
            $post->setContent($faker->paragraph($faker->numberBetween(3,6)));
            $post->setAuthorName($author);
            $post->setThread($thread);

            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
