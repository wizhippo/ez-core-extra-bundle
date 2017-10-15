<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Command;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssignSectionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('wizhippo:assign-section')
            ->setDescription("Assigns location to section")
            ->setDefinition(
                [
                    new InputArgument(
                        'locationId',
                        InputArgument::REQUIRED,
                        'An existing location id'
                    ),
                    new InputArgument(
                        'sectionId',
                        InputArgument::REQUIRED,
                        'An existing section id'
                    ),
                    new InputOption(
                        'depth',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'Depth limit',
                        -1
                    ),
                    new InputOption(
                        'dryrun',
                        null,
                        InputOption::VALUE_NONE,
                        'Dry run'
                    ),
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locationId = $input->getArgument('locationId');
        $sectionId = $input->getArgument('sectionId');

        /* @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $locationService = $repository->getLocationService();
        $sectionService = $repository->getSectionService();
        $userService = $repository->getUserService();

        $user = $userService->loadUser(14);
        $repository->setCurrentUser($user);

        try {
            $location = $locationService->loadLocation($locationId);
            $section = $sectionService->loadSection($sectionId);

            $this->assign(
                $input,
                $output,
                $locationService,
                $location,
                $sectionService,
                $section,
                $input->getOption('depth')
            );
        } catch (NotFoundException $e) {
            $output->writeln($e->getMessage());
        } catch (UnauthorizedException $e) {
            $output->writeln($e->getMessage());
        }
    }

    private function assign(
        InputInterface $input,
        OutputInterface $output,
        LocationService $locationService,
        Location $location,
        SectionService $sectionService,
        Section $section,
        $depth,
        $level = 0
    ) {
        if (!$input->getOption('dryrun')) {
            $sectionService->assignSection($location->contentInfo, $section);
        }

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln(str_repeat(" ", $level) . $location->contentInfo->name);
        }

        if ($depth < 0 || $depth - $level) {
            $children = $locationService->loadLocationChildren($location);
            foreach ($children->locations as $child) {
                $this->assign(
                    $input,
                    $output,
                    $locationService,
                    $child,
                    $sectionService,
                    $section,
                    $depth,
                    $level + 1
                );
            }
        }
    }
}
