<?php

declare(strict_types=1);

namespace App\Media\Transport\Command\Scheduler;

use Bro\WorldCoreBundle\Transport\Command\Traits\SymfonyStyleTrait;
use App\Media\Application\Service\Interfaces\MediaElasticsearchServiceInterface;
use App\Media\Infrastructure\Repository\MediaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @package App\Media\Transport\Command\Scheduler
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsCommand(
    name: self::NAME,
    description: 'Command to index medias in Elasticsearch on a schedule.',
)]
class IndexMediasScheduledCommand extends Command
{
    use SymfonyStyleTrait;

    final public const string NAME = 'scheduler:index-medias';

    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly MediaElasticsearchServiceInterface $mediaElasticsearchService
    ) {
        parent::__construct();
    }

    /**
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getSymfonyStyle($input, $output);
        $io->title('Elasticsearch indexation of medias');

        $medias = $this->mediaRepository->findAll();
        $count = 0;

        $this->mediaElasticsearchService->deleteIndex('medias');
        foreach ($medias as $media) {
            $this->mediaElasticsearchService->indexMediaInElasticsearch($media);
            $count++;
        }

        $message = sprintf('Indexation finish : %d medias indexed.', $count);
        $io->success($message);

        return Command::SUCCESS;
    }
}
