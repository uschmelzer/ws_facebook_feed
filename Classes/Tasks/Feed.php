<?php

namespace Webstobe\WsFacebookFeed\Tasks;

use TYPO3\CMS\Core\Utility\GeneralUtility;


class Feed extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

    //public $wsFacebookFeedAppId = NULL;
    public $wsFacebookFeedSecret = NULL;
    //public $wsFacebookFeedPageId = NULL;
    //public $wsFacebookFeedLocalFolder = NULL;
    //public $wsFacebookFeedLocalFile = NULL;

    public $settings = NULL;


    /**
     * Tries to get the feed from the facebook app and saves it to a json-file.
     *
     * @return boolean
     */
    public function execute()
    {

        // schmelzer, 2017-09-02
        // Get settings: Initialize ConfigurationManager
        // Get settings: Get all TypoScript (What a waste of resources)
        // Get settings: Extract the Typoscript of this extension
        $this->configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->settings = $extbaseFrameworkConfiguration['plugin.']['tx_wsfacebookfeed.']['settings.'];

        // maxEntries
        $this->settings['maxEntries'] = intval($this->settings['maxEntries']); // Clean
        if ($this->settings['maxEntries'] <= 0) {
            $this->settings['maxEntries'] = 10; // Defaultvalue
        }


        // schmelzer, 2017-09-02
        // Use a temp dir, not a custom dir
        // Still, it is stupid, to have to set the path here in the task and in the front end
        // https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/GlobalValues/Constants/Index.html

        // Set absolute paths
        $localFolder = PATH_site . $this->settings['feedDirPath'];
        $localFile = $localFolder . DIRECTORY_SEPARATOR . $this->settings['feedFilename'];

        $localTempFolder = $localFolder . DIRECTORY_SEPARATOR . 'temp';
        $localTempFile = $localTempFolder . DIRECTORY_SEPARATOR . $this->settings['feedFilename'];

        // Clean up duplicate slashes
        $localFolder = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localFolder);
        $localFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localFile);
        $localTempFolder = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localTempFolder);
        $localTempFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localTempFile);


        // Debug paths
        if (0) {
            throw new \BadMethodCallException (
                '$this->settings = ' . print_r($this->settings, true) . "\n" .
                '$localFolder = ' . $localFolder . "\n" .
                '$localFile = ' . $localFile . "\n" .
                '$localTempFolder = ' . $localTempFolder . "\n" .
                '$localTempFile = ' . $localTempFile . "\n"
                , 2);
            return FALSE;
        }

        $facebookGraphFields = array(
            'id',
            'object_id',
            'link',
            'source',
            'story',
            'caption',
            'created_time',
            'picture',
            'full_picture',
            'name',
            'message',
            'description',
            'type'
        );


        // Create graph URL
        $facebookStreamUrl = '';
        $facebookStreamUrl .= 'https://graph.facebook.com/' . $this->settings['pageName'];
        $facebookStreamUrl .= '/feed?fields=' . implode(',', $facebookGraphFields);
        $facebookStreamUrl .= '&access_token=' . $this->settings['pageId'] . '|' . $this->wsFacebookFeedSecret;
        $facebookStreamUrl .= '&limit=' . $this->settings['maxEntries'];

        // Query the graph
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_URL => $facebookStreamUrl
        ));
        $curlFacebookStreamResult = curl_exec($curl);
        $curlFacebookStreamHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($curlFacebookStreamHttpCode === 200) {

            // create main folder
            if (!is_dir($localFolder)) {
                mkdir($localFolder);
            }

            // create temporary folder
            if (!is_dir($localTempFolder)) {
                mkdir($localTempFolder);
            }

            // try to save the feed into the json
            if (!file_put_contents($localTempFile, $curlFacebookStreamResult)) {
                throw new \BadMethodCallException('Fehlschlag: Feed konnte nicht gespeichert werden.' . "\n", 2);
                return FALSE;
            } else {
                // move temporary file to final destination if download completed (filesize larger than zero)
                if (filesize($localTempFile) > 0) {
                    if (file_exists($localFile)) {
                        unlink($localFile);
                    }
                    copy($localTempFile, $localFile);
                }
            }


        } else {

            throw new \BadMethodCallException('Fehlschlag: Feed konnte nicht geladen werden (API Fehler).' . "\n", 2);
            return FALSE;

        }

        return TRUE;

    }


}