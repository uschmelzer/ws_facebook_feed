<?php
namespace Webstobe\WsFacebookFeed\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Cornel Widmer <cornel@webstobe.ch>, Webstobe GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 * @package ws_facebook_feed
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FacebookController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * Handles a request. The result output is returned by altering the given response.
     *
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request The request object
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response The response, modified by this handler
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @return void
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response) {
        try {
            parent::processRequest($request, $response);
        } catch (\TYPO3\CMS\Extbase\Property\Exception $exception) {
            $GLOBALS['TSFE']->pageNotFoundAndExit('Facebook Feed Controller could not load.');
        }
    }

    /**
     * Initializes the current action
     *
     * @return void
     */
    public function initializeAction() {

        // OVERRIDE WITH SETTINGS FROM THE FLEXFORM
        if (isset($this->settings['override']['feed']) && strlen($this->settings['override']['feed'])) {
            $this->settings['feed'] = $this->settings['override']['feed'];
        }
        if (isset($this->settings['override']['pageId']) && strlen($this->settings['override']['pageId'])) {
            $this->settings['pageId'] = $this->settings['override']['pageId'];
        }
        if (isset($this->settings['override']['pageName']) && strlen($this->settings['override']['pageName'])) {
            $this->settings['pageName'] = $this->settings['override']['pageName'];
        }
        if (isset($this->settings['override']['maxEntries']) && strlen($this->settings['override']['maxEntries'])) {
            $this->settings['maxEntries'] = $this->settings['override']['maxEntries'];
        }

    }

    /**
     * Action: Info
     *
     * Displays an occurring error message.
     *
     * @param \array $error
     *
     * @return void
     */
    public function infoAction($error = NULL) {

        if (is_array($this->request->getArguments()) && array_key_exists('error', $this->request->getArguments())) {
            $error = $this->request->getArgument('error');
        }

        if ($error === NULL) {
            $GLOBALS['TSFE']->pageNotFoundAndExit('Unexpected error occured. Info action not able to execute.');
        }

        $this->view->assign('error', $error);

    }

    /**
     * action show
     *
     * Shows the facebook feed
     *
     * @return void
     */
    public function showAction() {


        // OVERRIDE WITH SETTINGS FROM THE FLEXFORM
        if (isset($this->settings['override']['maxEntries']) && strlen($this->settings['override']['maxEntries'])) {
            $this->settings['maxEntries'] = $this->settings['override']['maxEntries'];
        }
        if (isset($this->settings['override']['pageId']) && intval($this->settings['override']['pageId']) !== 0) {
            $this->settings['pageId'] = $this->settings['override']['pageId'];
        }
        if (isset($this->settings['override']['pageName']) && strlen($this->settings['override']['pageName'])) {
            $this->settings['pageName'] = $this->settings['override']['pageName'];
        }
        if (isset($this->settings['override']['feed']) && strlen($this->settings['override']['feed'])) {
            $this->settings['feed'] = $this->settings['override']['feed'];
        }

        // schmelzer, 2017-09-01
        // Moved after the override, so the override values don't get ignored
        $maxEntries = $this->settings['maxEntries'];
        $pageId = $this->settings['pageId'];
        $pageName = $this->settings['pageName'];


        // schmelzer, 2017-09-01
        // Make paths relative to typo3 installation
        // https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/GlobalValues/Constants/Index.html
        // PATH_site = /absolute/path/to/documentroot/
        $feed = PATH_site . DIRECTORY_SEPARATOR . $this->settings['feed'];

        // Clean up duplicate DIRECTORY_SEPARATOR
        $feed = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $feed);


        if (file_exists($feed)) {

            $feedComplete = json_decode(file_get_contents($feed));
            $feedArray = array();
            $feedIterator = 1;

            if (is_object($feedComplete) && is_array($feedComplete->data)) {

                foreach ($feedComplete->data as $feedEntry) {

                    $feedEntryLink = 'https://www.facebook.com/' . $pageName . '/posts/' . str_replace($pageId . '_', '', $feedEntry->id);
                    $feedEntryImage = '';
                    $feedEntryMessage = '';

                    if (isset($feedEntry->message)) {
                        $feedEntryMessage = $feedEntry->message;
                    }

                    if (isset($feedEntry->description)) {
                        $feedEntryMessage = $feedEntry->description;
                    }

                    if (isset($feedEntry->object_id)) {
                        $feedEntryImage = 'https://graph.facebook.com/' . $feedEntry->object_id . '/picture?type=normal';
                    }

                    switch ($feedEntry->type) {
                        case 'event':
                            // do nothing
                            break;
                        default:

                            $feedArray[] = array(
                                'id' => $feedEntry->id,
                                'type' => $feedEntry->type,
                                'name' => $feedEntry->name,
                                'text' => $feedEntryMessage,
                                'link' => $feedEntryLink,
                                'image' => $feedEntryImage,
                                'date' => new \DateTime($feedEntry->created_time)
                            );

                            if ($feedIterator == $maxEntries) {
                                break(2);
                            } else {
                                $feedIterator++;
                            }

                    }
                }
            }

            $this->view->assign('feed', $feedArray);
            $this->view->assign('settings', $this->settings);

        } else {

            $this->forward('info', NULL, NULL, array(
                'error' => array(
                    'severity' => 'error',
                    'code' => 100,
                    // schmelzer
                    //'message' => 'The feed file was not found in the specific path.'
                    'message' => 'The feed file was not found in the specific path: "'. $feed .'"'
                )
            ));

        }

    }

}