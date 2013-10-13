<?php

/**
 * Solr spelling listener.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2013.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */

namespace VuFind\Search\Solr;

use VuFindSearch\Service;
use VuFindSearch\Backend\BackendInterface;
use VuFindSearch\Backend\Solr\Response\Json\Spellcheck;
use VuFindSearch\ParamBag;
use VuFindSearch\Query\Query;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

/**
 * Solr spelling listener.
 *
 * @category VuFind2
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class InjectSpellingListener
{
    /**
     * Backend.
     *
     * @var BackendInterface
     */
    protected $backend;

    /**
     * Is spelling active?
     *
     * @var bool
     */
    protected $active = false;

    /**
     * Constructor.
     *
     * @param BackendInterface $backend      Backend
     * @param array            $dictionaries Spelling dictionaries to use.
     *
     * @return void
     */
    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Attach listener to shared event manager.
     *
     * @param SharedEventManagerInterface $manager Shared event manager
     *
     * @return void
     */
    public function attach(SharedEventManagerInterface $manager)
    {
        $manager->attach(
            'VuFind\Search', Service::EVENT_PRE, array($this, 'onSearchPre')
        );
    }

    /**
     * Set up spelling parameters.
     *
     * @param EventInterface $event Event
     *
     * @return EventInterface
     */
    public function onSearchPre(EventInterface $event)
    {
        $backend = $event->getTarget();
        if ($backend === $this->backend) {
            $params = $event->getParam('params');
            if ($params) {
                // Set spelling parameters unless explicitly disabled:
                $sc = $params->get('spellcheck');
                if (!isset($sc[0]) || $sc[0] != 'false') {
                    $this->active = true;

                    // Set relevant Solr parameters:
                    $params->set('spellcheck', 'true');

                    // Turn on spellcheck.q generation in query builder:
                    $this->backend->getQueryBuilder()
                        ->setCreateSpellingQuery(true);
                }
            }
        }
        return $event;
    }
}
