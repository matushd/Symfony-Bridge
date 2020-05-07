<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Bridge\Symfony;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container as SymfonyContainer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Replacement for the Symfony service container.
 *
 * This container extends Symfony's container with a fallback container when an entry is not found.
 * That way, we can put PHP-DI's container as a fallback to Symfony's.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SymfonyContainerBridge extends SymfonyContainer
{
    /**
     * @var ContainerInterface|null
     */
    private $fallbackContainer;

    /**
     * @param ContainerInterface $container
     */
    public function setFallbackContainer(ContainerInterface $container): void
    {
        $this->fallbackContainer = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getFallbackContainer(): ContainerInterface
    {
        return $this->fallbackContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id): bool
    {
        if (parent::has($id)) {
            return true;
        }

        if (! $this->fallbackContainer) {
            return false;
        }

        return $this->fallbackContainer->has($id);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (parent::has($id)) {
            return parent::get($id, $invalidBehavior);
        }

        if (! $this->fallbackContainer) {
            return false;
        }

        try {
            $entry = $this->fallbackContainer->get($id);

            // Stupid hack for Symfony's ContainerAwareInterface
            if ($entry instanceof ContainerAwareInterface) {
                $entry->setContainer($this);
            }

            return $entry;
        } catch (NotFoundExceptionInterface $e) {
            if ($invalidBehavior === self::EXCEPTION_ON_INVALID_REFERENCE) {
                throw new ServiceNotFoundException($id, null, $e);
            }
        }

        return null;
    }
}
