<?php

namespace Webstobe\WsFacebookFeed\Tasks;


class Feed extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

    public $wsFacebookFeedAppId = NULL;
    public $wsFacebookFeedSecret = NULL;
    public $wsFacebookFeedPageId = NULL;
    public $wsFacebookFeedLocalFolder = NULL;
    public $wsFacebookFeedLocalFile = NULL;

    /**
     * Tries to get the feed from the facebook app and saves it to a json-file.
     *
     * @return boolean
     */
    public function execute()
    {

        // schmelzer, 2017-09-01
        // Make paths relative to typo3 installation
        // https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/GlobalValues/Constants/Index.html
        // PATH_site = /absolute/path/to/documentroot

        // Set absolute paths
        $localFolder = PATH_site . DIRECTORY_SEPARATOR . $this->wsFacebookFeedLocalFolder;
        $localFile = $localFolder . DIRECTORY_SEPARATOR . $this->wsFacebookFeedLocalFile;
        $tempFolder = 'temp';
        $localTempFolder = $localFolder . DIRECTORY_SEPARATOR . $tempFolder;
        $localTempFile = $localTempFolder . DIRECTORY_SEPARATOR . $this->wsFacebookFeedLocalFile;

        // Clean up duplicate DIRECTORY_SEPARATOR
        $localFolder = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localFolder);
        $localFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localFile);
        $localTempFolder = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localTempFolder);
        $localTempFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localTempFile);

        // Do it twice
        $localFolder = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localFolder);
        $localFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localFile);
        $localTempFolder = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localTempFolder);
        $localTempFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $localTempFile);

        /*
        // Debug paths
        throw new \BadMethodCallException(
            '$localFolder' . $localFolder . "\n" .
            '$localFile' . $localFile . "\n" .
            '$localTempFolder' . $localTempFolder . "\n" .
            '$localTempFile' . $localTempFile . "\n"
            , 2);
        return FALSE;
        */

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

        $facebookStreamUrl = 'https://graph.facebook.com/' . $this->wsFacebookFeedPageId . '/feed?fields=' . implode(',', $facebookGraphFields) . '&access_token=' . $this->wsFacebookFeedAppId . '|' . $this->wsFacebookFeedSecret . '&limit=10';

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