<?php

namespace Shopline\Bundle\PDFDesignerBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Knp\Menu\ItemInterface;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

class NavigationListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider = null;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param EntityManager  $entityManager
     * @param ConfigProvider $entityConfigProvider
     * @param SecurityFacade $securityFacade
     * @param AclHelper      $aclHelper
     */
    public function __construct(
        EntityManager $entityManager,
        ConfigProvider $entityConfigProvider,
        SecurityFacade $securityFacade,
        AclHelper $aclHelper
    ) {
        $this->em                   = $entityManager;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->securityFacade       = $securityFacade;
        $this->aclHelper            = $aclHelper;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        
        /** @var ItemInterface $reportsMenuItem */
        $reportsMenuItem = $event->getMenu()->getChild('shopline_pdfdesigner');
        if ($reportsMenuItem && $this->securityFacade->hasLoggedUser()) {

            $qb = $this->em->getRepository('ShoplinePDFDesignerBundle:DesignerTemplate')
                ->createQueryBuilder('designer')
                ->orderBy('designer.name', 'ASC');
            $reports = $this->aclHelper->apply($qb)->execute();

            if (!empty($reports)) {
                $this->addDivider($reportsMenuItem);
                $reportMenuData = [];
                foreach ($reports as $report) {
                    $config      = $this->entityConfigProvider->getConfig($report->getEntityName());
                    $entityLabel = $config->get('plural_label');
                    if (!isset ($reportMenuData[$entityLabel])) {
                        $reportMenuData[$entityLabel] = [];
                    }
                    $reportMenuData[$entityLabel][$report->getId()] = $report->getName();
                }
                $this->buildReportMenu($reportsMenuItem, $reportMenuData);
            }
        }
    }

    /**
     * Build report menu
     *
     * @param ItemInterface $reportsItem
     * @param array         $reportData
     *  key => entity label
     *  value => array of reports id's and label's
     */
    protected function buildReportMenu(ItemInterface $reportsItem, $reportData)
    {
        foreach ($reportData as $entityLabel => $reports) {
            foreach ($reports as $reportId => $reportLabel) {
                $this->getEntityMenuItem($reportsItem, $entityLabel)
                    ->addChild(
                        $reportLabel . '_report',
                        [
                            'label'           => $reportLabel,
                            'route'           => 'shopline_template_view',
                            'routeParameters' => [
                                'id' => $reportId
                            ]
                        ]
                    );
            }
        }
    }

    /**
     * Adds a divider to the given menu
     *
     * @param ItemInterface $menu
     */
    protected function addDivider(ItemInterface $menu)
    {
        $menu->addChild('divider-' . rand(1, 99999))
            ->setLabel('')
            ->setAttribute('class', 'divider')
            ->setExtra('position', 15); // after manage report, we have 10 there
    }

    /**
     * Get entity menu item for report item
     *
     * @param ItemInterface $reportItem
     * @param string        $entityLabel
     * @return ItemInterface
     */
    protected function getEntityMenuItem(ItemInterface $reportItem, $entityLabel)
    {
        $entityItemName = $entityLabel . '_report_tab';
        $entityItem     = $reportItem->getChild($entityItemName);
        if (!$entityItem) {
            $reportItem->addChild(
                $entityItemName,
                [
                    'label' => $entityLabel,
                    'uri'   => '#',
                    // after divider, all entities will be added in EntityName:ASC order
                    'extras'=> ['position' => 20]
                ]
            );
            $entityItem = $reportItem->getChild($entityItemName);
        }

        return $entityItem;
    }
}