<?php
/**
 * Solr spelling processor.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2011.
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
 * @package  Search_Solr
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
namespace VuFind\Search\Solr;
use VuFindSearch\Backend\Solr\Response\Json\Spellcheck;
use VuFindSearch\Query\AbstractQuery;
use Zend\Config\Config;

/**
 * Solr spelling processor.
 *
 * @category VuFind2
 * @package  Search_Solr
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
class SpellingProcessor
{
    /**
     * Spelling limit
     *
     * @var int
     */
    protected $spellingLimit = 3;

    /**
     * Spell check words with numbers in them?
     *
     * @var bool
     */
    protected $spellSkipNumeric = true;

    /**
     * Offer expansions on terms as well as basic replacements?
     *
     * @var bool
     */
    protected $expand = true;

    /**
     * Constructor
     *
     * @param Config $config Spelling configuration (optional)
     */
    public function __construct($config = null)
    {
        if (isset($config->limit)) {
            $this->spellingLimit = $config->limit;
        }
        if (isset($config->skip_numeric)) {
            $this->spellSkipNumeric = $config->skip_numeric;
        }
        if (isset($config->expand)) {
            $this->expand = $config->expand;
        }
    }

    /**
     * Are we skipping numeric words?
     *
     * @return bool
     */
    public function shouldSkipNumericSpelling()
    {
        return $this->spellSkipNumeric;
    }


    /**
     * Get the spelling limit.
     *
     * @return int
     */
    public function getSpellingLimit()
    {
        return $this->spellingLimit;
    }

    /**
     * Get collated spelling suggestions for a query.
     *
     * @param Spellcheck    $spellcheck Complete spellcheck information
     * @param AbstractQuery $query      Query for which info should be retrieved
     *
     * @return array
     */
    public function getCollations(Spellcheck $spellcheck, AbstractQuery $query)
    {   
	    $suggestionLimit = $this->getSpellingLimit();
	    $collations = array();
		
		// don't continue if the original query was entirely numeric
        if ($this->shouldSkipNumericSpelling() && is_numeric($query->getString())) {
            return $collations;
        }

        // don't continue if we've already done an expansion
        if (strpos($query->getString(), ') OR (') != false) {
            return $collations;
        }

        $rawCollations = $spellcheck->getCollations();
        foreach ($rawCollations as $rawCollation) {
            if (count($collations) >= $suggestionLimit) {
                break;
            }
            $collation = array('collation' => $rawCollation, 'expand_collation' => '');
            // Only generate expansions if enabled in config
            if ($this->expand) {
                $collation['expand_collation'] = '(' . $query->getString() .') OR (' . $rawCollation . ')';
            }
            array_push($collations, $collation);
        }
        return $collations;
    }
}