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

namespace Lotgd\Bundle\SpookyGoldBundle;

use Lotgd\Bundle\Contract\LotgdBundleInterface;
use Lotgd\Bundle\Contract\LotgdBundleTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LotgdSpookyGoldBundle extends Bundle implements LotgdBundleInterface
{
    use LotgdBundleTrait;

    public const TRANSLATION_DOMAIN = 'bundle_spooky_gold';

    /**
     * {@inheritDoc}
     */
    public function getLotgdName(): string
    {
        return 'Spooky Gold';
    }

    /**
     * {@inheritDoc}
     */
    public function getLotgdVersion(): string
    {
        return '0.1.0';
    }

    /**
     * {@inheritDoc}
     */
    public function getLotgdIcon(): string
    {
        return 'ghost icon';
    }

    /**
     * {@inheritDoc}
     */
    public function getLotgdDescription(): string
    {
        return 'Just a silly little village special that is mainly for the "what the heck was that?" response. Generally a gem or gold is found, with a slight chance of something bad or good happening.';
    }

    /**
     * {@inheritDoc}
     */
    public function getLotgdDownload(): string
    {
        return 'https://github.com/lotgd-core/spooky-gold-bundle';
    }
}
