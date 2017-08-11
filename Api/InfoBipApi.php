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
        $remove_MauticTrackingUrl = true;

        //****** This block removes Mautic tracking URL from the message body. Although it is desirable due to length limitations, it may cause a performance degradation.
        if($remove_MauticTrackingUrl){
            
            preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $matches);
            $messageLinks = $matches;

            foreach($messageLinks as $messageLink) {
                if(strlen($messageLink[0]) < 5){
                    continue;
                }

                $resolvedUrl = $messageLink[0];
                $maxJumps = 0;
                while(parse_url($resolvedUrl, PHP_URL_HOST) == parse_url($messageLink[0], PHP_URL_HOST) && $maxJumps < 3){
                    stream_context_set_default(
                        array(
                            'http' => array(
                                'method' => 'GET'
                            )
                        )
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $resolvedUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER,true);

                    $result = curl_exec($ch);

                    curl_close($ch);

                    list($headers, $content) = explode("\r\n\r\n",$result,2);

                    foreach (explode("\r\n",$headers) as $hdr){
                        if(strpos($hdr, 'Location') === false){
                            continue;
                        }
                        
                        $resolvedUrl = str_replace('Location: ', '', $hdr);
                            break;
                    }
                    $maxJumps++;
                }
                $messageBody = str_replace($messageLink[0], $resolvedUrl, $messageBody);
            }
        }
        //****** End of Mautic tracking URL removal

        try{
            $number = '+55' . $number;
            
            $url = "http://api.infobip.com/sms/1/text/single";
            $curl = curl_init();
            
            $headers = [
                'Authorization: Basic '. base64_encode("{$this->username}:{$this->password}"),
                'Content-Type:application/json',
                'Accept: application/json'
            ];
            
            $data = [
                'from' => "InfoSMS",
                'to' => $number,
                'text' => $messageBody
            ];
            
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

        // if ($number === null) {
        //     return false;
        // }

        // try {
        //     $this->client->account->messages->sendMessage(
        //         $this->sendingPhoneNumber,
        //         $this->sanitizeNumber($number),
        //         $content
        //     );

        //     return true;
        // } catch (\Services_InfoBip_RestException $e) {
        //     $this->logger->addWarning(
        //         $e->getMessage(),
        //         ['exception' => $e]
        //     );

        //     return $e->getMessage();
        // } catch (NumberParseException $e) {
        //     $this->logger->addWarning(
        //         $e->getMessage(),
        //         ['exception' => $e]
        //     );

        //     return $e->getMessage();
        // }
    }
}
