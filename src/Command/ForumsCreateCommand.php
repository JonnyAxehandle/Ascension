<?php

namespace App\Command;

use App\Entity\Channel;
use App\Entity\Forum;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'forums:create',
    description: 'Create forums and categories',
)]
class ForumsCreateCommand extends Command
{
    /**
     * @var ForumRepository
     */
    private ForumRepository $forumRepository;
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * ForumsCreateCommand constructor.
     * @param ForumRepository $forumRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ForumRepository $forumRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->forumRepository = $forumRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('category', InputArgument::OPTIONAL, 'Create a category')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('category')) {
            return $this->executeCreateCategory($input, $output);
        }

        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $categories = $this->forumRepository->findCategories();
        if (!count($categories)) {
            $io->error("You have no categories. Please create at least one with `forums:create category` before creating a forum");
            return Command::FAILURE;
        }

        while (true) {
            $io->title("Available categories");
            $categoryIds = [];
            foreach ($categories as $category) {
                $categoryIds[] = $category->getId();
                $io->writeln(sprintf("%d: %s", $category->getId(), $category->getTitle()));
            }

            $categoryId = $io->ask("Please enter a category ID");
            if ($categoryId != 0 && !filter_var($categoryId, FILTER_VALIDATE_INT)) {
                $io->error(sprintf('%1$s? %2$s!? %1$s ain\'t no number I ever heard of!', $categoryId, strtoupper($categoryId)));
                continue;
            }

            if (array_search($categoryId, $categoryIds) === false) {
                $io->error("Invalid ID");
                continue;
            }

            while (true) {
                $forumTitle = trim($io->ask("New forum title"));

                if ($forumTitle == "") {
                    $io->error("Forum title cannot be blank");
                    continue;
                }

                if ($this->forumRepository->findBy(['Title' => $forumTitle])) {
                    $question = new ConfirmationQuestion('A forum/category with this title already exists. Are you sure?' . PHP_EOL, false);
                    if (!$helper->ask($input, $output, $question)) {
                        continue;
                    }
                }

                $newForum = new Forum();
                $newForum->setChannel(new Channel());
                $newForum->setParent($this->forumRepository->find($categoryId));
                $newForum->setTitle($forumTitle);

                $this->entityManager->persist($newForum);
                $this->entityManager->flush();

                $io->success("New forum created");

                break;
            }

            $question = new ConfirmationQuestion('Create another?' . PHP_EOL, false);
            if ($helper->ask($input, $output, $question)) {
                continue;
            }

            break;
        }

        return Command::SUCCESS;
    }

    private function executeCreateCategory(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        while(true) {
            $categoryTitle = trim($io->ask("New category title"));

            if ($categoryTitle == "") {
                $io->error("Category title cannot be blank");
                continue;
            }

            if ($this->forumRepository->findBy(['Title' => $categoryTitle])) {
                $question = new ConfirmationQuestion('A forum/category with this title already exists. Are you sure?' . PHP_EOL, false);
                if (!$helper->ask($input, $output, $question)) {
                    continue;
                }
            }

            $newCategory = new Forum();
            $newCategory->setChannel(new Channel());
            $newCategory->setTitle($categoryTitle);

            $this->entityManager->persist($newCategory);
            $this->entityManager->flush();

            $io->success("New category created");

            $question = new ConfirmationQuestion('Create another?' . PHP_EOL, false);
            if ($helper->ask($input, $output, $question)) {
                continue;
            }

            break;
        }

        return Command::SUCCESS;
    }
}
