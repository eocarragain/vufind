<?php
/**
 * Helper class for displaying search-related HTML chunks.
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace VuFind\View\Helper;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper class for displaying search-related HTML chunks.
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
abstract class AbstractSearch extends AbstractHelper
{
    /**
     * Get the CSS classes for the container holding the suggestions.
     *
     * @return string
     */
    abstract protected function getContainerClass();

    /**
     * Render an expand link.
     *
     * @param string                          $url  Link href
     * @param \Zend\View\Renderer\PhpRenderer $view View renderer object
     *
     * @return string
     */
    abstract protected function renderExpandLink($url, $view);

    /**
     * Support function to display spelling suggestions.
     *
     * @param string                          $msg     HTML to display at the top of
     * the spelling section.
     * @param \VuFind\Search\Base\Results     $results Results object
     * @param \Zend\View\Renderer\PhpRenderer $view    View renderer object
     *
     * @return string
     */
    public function renderSpellingSuggestions($msg, $results, $view)
    {
        $spellingCollations = $results->getSpellingCollations();
        if (empty($spellingCollations)) {
            return '';
        }
        $displayQuery = $results->getParams()->getDisplayQuery();
        $resultsTotal = $results->getResultTotal();

        $html = '<div class="' . $this->getContainerClass() . '">';
        $html .= $msg;
        $html .= '<br/>' . $this->view->escapeHtml($displayQuery) . ' &raquo; ';
        $i = 0;
        foreach ($spellingCollations as $collation) {
            if ($i++ > 0) {
               $html .= ', ';
            }
            $html .= '<a href="' . $results->getUrlQuery()
                                     ->replaceTerm($displayQuery, $collation['collation']) . '">' . $collation['collation'] . '</a>';
            // Only offer expansions if the initial query gave at least one result
            if ($resultsTotal > 0 && $collation['expand_collation'] != '') {
                $url = $results->getUrlQuery()
                    ->replaceTerm($displayQuery, $collation['expand_collation']); 
                $html .= $this->renderExpandLink($url, $view);
            }
        }
        $html .= '</div>';
        return $html;
    }
}