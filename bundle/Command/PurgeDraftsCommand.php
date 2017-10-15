<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeDraftsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('wizhippo:purge-drafts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->getPermissionResolver()->setCurrentUserReference(new UserReference(14));
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();
        $contentService = $repository->getContentService();

        $query = new Query([
            'query' => new Criterion\LogicalAnd(
                [
                    new Criterion\ContentTypeIdentifier(['user', 'associate_user']),
                ]
            ),
        ]);

        $searchResult = $searchService->findContentInfo($query);
        $progress = new ProgressBar($output, $searchResult->totalCount);

        foreach ($searchResult->searchHits as $searchHit) {
            $user = $userService->loadUser($searchHit->valueObject->id);
            $drafts = $contentService->loadContentDrafts($user);
            foreach ($drafts as $draft) {
                $contentService->deleteVersion($draft);
            }
            $progress->advance();
        }

        $progress->finish();
    }
}
