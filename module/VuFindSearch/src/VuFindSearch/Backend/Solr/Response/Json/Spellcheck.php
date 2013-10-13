<?php

/**
 * SOLR spellcheck information.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */

namespace VuFindSearch\Backend\Solr\Response\Json;

use IteratorAggregate;
use ArrayObject;
use Countable;

/**
 * SOLR spellcheck information.
 *
 * @category VuFind2
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
class Spellcheck implements IteratorAggregate, Countable
{
    /**
     * Spellcheck collated terms
     *
     * @var ArrayObject
     */
    protected $collations;

    /**
     * Spelling query that generated suggestions
     *
     * @var string
     */
    protected $query;

    /**
     * Constructor.
     *
     * @param array  $spellcheck SOLR spellcheck information
     * @param string $query      Spelling query that generated suggestions
     *
     * @return void
     */
    public function __construct(array $spellcheck, $query)
    {
        $this->collations = new ArrayObject();
        $list = new NamedList($spellcheck);
        foreach ($list as $term => $info) {
            if ($term == "collation") {
                $this->collations->append($info);
            }
        }
        $this->query = $query;
    }

    /**
     * Get spelling query.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get spelling collations.
     *
     * @return ArrayObject
     */
    public function getCollations()
    {
        return $this->collations;
    }

    /// IteratorAggregate

    /**
     * Return aggregated iterator.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->terms->getIterator();
    }

    /// Countable

    /**
     * Return number of terms.
     *
     * @return integer
     */
    public function count()
    {
        return $this->terms->count();
    }

}