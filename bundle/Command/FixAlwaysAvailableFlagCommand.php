<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wizhippo\eZ\Publish\Core\Pagination\Pagerfanta\SearchHandlerAdapter;

class FixAlwaysAvailableFlagCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('wizhippo:fix-always-available-flag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->getPermissionResolver()->setCurrentUserReference(new UserReference(14));
        $gateway = $this->getContainer()->get('ezpublish.persistence.legacy.content.gateway.inner');

        // We use legacy here to make sure we traverse all content as items might not be indexed yet by other
        // search engines
        // TODO: replace when correct api available
        $query = new Query();
        $pager = new Pagerfanta(new SearchHandlerAdapter($query,
            $this->getContainer()->get('ezpublish.spi.search.legacy')));
        $pager->setMaxPerPage(100);

        $progress = new ProgressBar($output, $pager->getNbResults());

        do {
            foreach ($pager->getCurrentPageResults() as $searchHit) {
                $contentInfo = $repository->getContentService()->loadContentInfo($searchHit->valueObject->id);
                $contentType = $repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
                $gateway->updateAlwaysAvailableFlag($contentInfo->id, $contentType->defaultAlwaysAvailable);
                $progress->advance();
            }
        } while ($pager->hasNextPage() && $pager->setCurrentPage($pager->getNextPage()));

        $progress->finish();
    }
}
