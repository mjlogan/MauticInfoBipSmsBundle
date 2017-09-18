<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      André Rocha
 *
 * @link        http://mjlogan.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticInfoBipSmsBundle\Api;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\CoreBundle\Helper\PhoneNumberHelper;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Monolog\Logger;

class InfoBipApi extends AbstractSmsApi
{
    private $username;
    private $password;



    /**
     * @var \Services_InfoBip
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $sendingPhoneNumber;

    /**
     * InfoBipApi constructor.
     *
     * @param TrackableModel    $pageTrackableModel
     * @param PhoneNumberHelper $phoneNumberHelper
     * @param IntegrationHelper $integrationHelper
     * @param Logger            $logger
     */

    // public function __construct(TrackableModel $pageTrackableModel, PhoneNumberHelper $phoneNumberHelper, IntegrationHelper $integrationHelper, Logger $logger, $username, $password)
    public function __construct(TrackableModel $pageTrackableModel, PhoneNumberHelper $phoneNumberHelper, IntegrationHelper $integrationHelper, Logger $logger)
    {
        $this->logger = $logger;

        $integration = $integrationHelper->getIntegrationObject('InfoBip');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $this->sendingPhoneNumber = $integration->getIntegrationSettings()->getFeatureSettings()['sending_phone_number'];

            $keys = $integration->getDecryptedApiKeys();

            //$this->client = new \Services_InfoBip($keys['username'], $keys['password']);
            $this->username = $keys['username'];
            $this->password = $keys['password'];
        }

        parent::__construct($pageTrackableModel);
    }

    /**
     * @param string $number
     *
     * @return string
     */
    protected function sanitizeNumber($number)
    {
        $util   = PhoneNumberUtil::getInstance();
        $parsed = $util->parse($number, 'US');

        return $util->format($parsed, PhoneNumberFormat::E164);
    }

    /**
     * @param string $number
     * @param string $content
     *
     * @return bool|string
     */
    public function sendSms($number, $content)
    {
        if ($number === null) {
            return false;
        }

        $messageBody = $content;

        try{
            $number = '+55' . $number;
            
            $url = "http://api.infobip.com/sms/1/text/single";
            
            $headers = [
                'Authorization: Basic '. base64_encode("{$this->username}:{$this->password}"),
                'Content-Type:application/json',
                'Accept: application/json'
            ];
            
            $data = [
                'from' => "InfoSMS",
                'to' => $number,
                'text' => $messageBody,
                'language' => ['languageCode' => 'PT'],
                'transliteration' => 'NON_UNICODE'
            ];

            $curl = curl_init();            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_exec($curl);
            curl_close($curl);
        }
        catch(Exception $e) {
            $this->logger->addWarning(
                $e->getMessage(),
                ['exception' => $e]
            );
            return false;
        }

		return true;
    }
}
