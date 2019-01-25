<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wizhippo\eZ\Publish\Core\Pagination\Pagerfanta\SearchHandlerAdapter;

class FixOwnerSelfCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('wizhippo:fix-owner-self');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->getPermissionResolver()->setCurrentUserReference(new UserReference(14));
        $contentService = $repository->getContentService();

        // We use legacy here to make sure we traverse all content as items might not be indexed yet by other
        // search engines
        // TODO: replace when correct api available
        $query = new Query([
            'filter' => new Criterion\LogicalAnd([
                new Criterion\ParentLocationId(56),
                new Criterion\ContentTypeIdentifier('user'),
            ]),
        ]);
        $pager = new Pagerfanta(new SearchHandlerAdapter($query,
            $this->getContainer()->get('ezpublish.spi.search.legacy')));
        $pager->setMaxPerPage(100);

        $progress = new ProgressBar($output, $pager->getNbResults());

        do {
            foreach ($pager->getCurrentPageResults() as $searchHit) {
                $siteOwnerUserContentInfo = $repository->getContentService()->loadContentInfo($searchHit->valueObject->id);
                if ($siteOwnerUserContentInfo->ownerId !== $searchHit->valueObject->id) {
                    $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
                    $contentMetadataUpdateStruct->ownerId = $searchHit->valueObject->id;
                    $contentService->updateContentMetadata($siteOwnerUserContentInfo, $contentMetadataUpdateStruct);
                }
                $progress->advance();
            }
        } while ($pager->hasNextPage() && $pager->setCurrentPage($pager->getNextPage()));

        $progress->finish();
    }
}
