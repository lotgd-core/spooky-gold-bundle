<?php

/**
 * This file is part of "LoTGD Bundle Spooky Gold".
 *
 * @see https://github.com/lotgd-core/spooky-gold-bundle
 *
 * @license https://github.com/lotgd-core/spooky-gold-bundle/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 0.1.0
 */

namespace Lotgd\Bundle\SpookyGoldBundle\EventSubscriber;

use Lotgd\Core\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SpookyGoldSubscriber implements EventSubscriberInterface
{
    public function newday()
    {
        set_module_pref('visits', 0, 'bundle_spooky_gold');
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::PAGE_NEWDAY => 'newday',
        ];
    }
}
