<?php

namespace KudinovFedor\SmsFly;

use Exception;

/**
 * Class SmsFly
 * @package KudinovFedor\SmsFly
 * @author Kudinov Fedor <admin@joompress.biz>
 * @license https://opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/kudinovfedor/sms-fly
 */
class SmsFly
{
    const VERSION = '0.0.2';

    /**
     * Service Address
     */
    const API_URL = 'http://sms-fly.com/api/api.php';

    const RATE_MIN = 1;
    const RATE_MAX = 120;

    const LIFE_TIME_MIN = 1;
    const LIFE_TIME_MAX = 24;

    /**
     * Speed of sending message (s) in number of messages per minute.
     *
     * @description Only integer values in the range 1 to 120 are allowed.
     *
     * @var int
     */
    private $rate = 1;

    /**
     * Lifetime of message (s) in hours
     *
     * @description Only integer values in the range 1 to 24 are allowed.
     *
     * @var int
     */
    private $lifeTime = 4;

    /**
     * The time at which the message (s) were sent.
     * Format AUTO or YYYY-MM-DD HH: MM: SS.
     *
     * @description The system allows a time correction of 5 minutes. format for PHP “Y-m-d H: i: s”).
     * If you select AUTO - the current system time will be set - immediate sending.
     *
     * @var string
     */
    private $startTime = 'AUTO';

    /**
     * End time of message (s) sending,
     * Format AUTO or YYYY-MM-DD HH: MM: SS.
     *
     * @description Cannot be earlier than the start time of dispatch. (format for PHP “Y-m-d H: i: s”).
     * You can use the AUTO value to automatically calculate the time by the system.
     *
     * @var string
     */
    private $endTime = 'AUTO';

    /**
     * Description of the campaign (displayed in the web interface).
     *
     * @description It does not affect the mailing list itself. Can be left blank.
     *
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string|array
     */
    private $recipient;

    /**
     * Mailing identifier in the system.
     *
     * @var int|string
     */
    private $campaignId;

    /**
     * Sender. Specifies an alphanumeric name (alpha name).
     *
     * @description Only alphanumeric names registered for the user are allowed.
     *
     * @var string
     */
    private $source = 'InfoCentr';

    /**
     * @var ParseXml
     */
    private $parseXML;

    /**
     * Statuses
     *
     * @var array
     */
    private $messages = [
        'DENIED' => 'Неверные учетные данные!',
    ];

    /**
     * SmsFly constructor.
     *
     * @param array $args
     */
    public function __construct($args = [])
    {
        if (!empty($args['login'])) {
            $this->login = $args['login'];
        }
        
        if (!empty($args['password'])) {
            $this->password = $args['password'];
        }
        
        if (!empty($args['from'])) {
            $this->setFrom($args['from']);
        }

        $this->parseXML = new ParseXML();
    }

    /**
     * Set login
     *
     * @param string $login
     * @return SmsFly
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return SmsFly
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set from
     *
     * @param string $from
     * @return SmsFly
     */
    public function setFrom($from)
    {
        $this->source = htmlspecialchars($from);

        return $this;
    }

    /**
     * Set rate
     *
     * @param int $rate
     * @return SmsFly
     */
    public function setRate($rate)
    {
        if (is_int($rate) && $rate >= self::RATE_MIN && $rate <= self::RATE_MAX) {
            $this->rate = $rate;
        }

        return $this;
    }

    /**
     * Set life time
     *
     * @param int $lifeTime
     * @return SmsFly
     */
    public function setLifeTime($lifeTime)
    {
        if (is_int($lifeTime) && $lifeTime >= self::LIFE_TIME_MIN && $lifeTime <= self::LIFE_TIME_MAX) {
            $this->lifeTime = $lifeTime;
        }

        return $this;
    }

    /**
     * Set start time
     *
     * @param string $startTime
     * @return SmsFly
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Set end time
     *
     * @param string $endTime
     * @return SmsFly
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Set to
     *
     * @param string|array $to
     * @return SmsFly
     */
    public function setTo($to)
    {
        if (is_array($to)) {

            $recipient = array_map(function ($phone) {
                return $this->formatPhoneNumber($phone);
            }, $to);

        } else {

            $recipient = $this->formatPhoneNumber($to);

        }

        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return SmsFly
     */
    public function setMessage($message)
    {
        $this->message = htmlspecialchars($message);

        return $this;
    }

    /**
     * Set campaign id
     *
     * @param int|string $campaignId
     * @return SmsFly
     */
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;

        return $this;
    }

    /**
     * Set messages
     *
     * @param array $messages
     * @return SmsFly
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Send SMS
     *
     * @param array $args
     * @return mixed
     */
    public function sendSMS($args = [])
    {
        $default = [
            'to' => $this->recipient,
            'message' => $this->message,
        ];

        $args = array_merge($default, $args);

        $xml = $this->startXml();
        $xml .= '<request><operation>SENDSMS</operation>';
        $xml .= sprintf(
            '<message start_time="%s" end_time="%s" lifetime="%d" rate="%d" desc="%s" source="%s" version="%s">' . PHP_EOL,
            $this->startTime, $this->endTime, $this->lifeTime, $this->rate, $this->description, $this->source, self::VERSION
        );
        $xml .= '<body>' . $args['message'] . '</body>';

        if (is_array($args['to'])) {
            foreach ($args['to'] as $phone) {
                $xml .= '<recipient>' . $phone . '</recipient>';
            }
        } else {
            $xml .= '<recipient>' . $args['to'] . '</recipient>';
        }

        $xml .= '</message></request>';

        return $this->sendRequest($xml, 'sendSMS');
    }

    /**
     * Get balance
     *
     * @return mixed
     */
    public function getBalance()
    {
        $xml = $this->startXml();
        $xml .= '<request><operation>GETBALANCE</operation></request>';

        return $this->sendRequest($xml, 'balance');
    }

    /**
     * Get campaign info
     *
     * @param int|string $campaignId
     * @return mixed
     */
    public function getCampaignInfo($campaignId = null)
    {
        $campaignId = is_numeric($campaignId) ? $campaignId : $this->campaignId;

        $xml = $this->startXml();
        $xml .= '<request><operation>GETCAMPAIGNINFO</operation><message campaignID="' . $campaignId . '" /></request>';

        return $this->sendRequest($xml, 'campaignInfo');
    }

    /**
     * Get campaign detail
     *
     * @param int|string $campaignId
     * @return mixed
     */
    public function getCampaignDetail($campaignId = null)
    {
        $campaignId = is_numeric($campaignId) ? $campaignId : $this->campaignId;

        $xml = $this->startXml();
        $xml .= '<request><operation>GETCAMPAIGNDETAIL</operation><message campaignID="' . $campaignId . '" /></request>';

        return $this->sendRequest($xml, 'campaignDetail');
    }

    /**
     * Get message status
     *
     * @param string $recipient
     * @param int|string $campaignId
     * @return mixed
     */
    public function getMessageStatus($recipient = null, $campaignId = null)
    {
        $recipient = is_string($campaignId) ? $recipient : $this->recipient;
        $campaignId = is_numeric($campaignId) ? $campaignId : $this->campaignId;

        $xml = $this->startXml();
        $xml .= '<request><operation>GETMESSAGESTATUS</operation><message campaignID="' . $campaignId . '" recipient="' . $recipient . '" /></request>';

        return $this->sendRequest($xml, 'messageStatus');
    }

    /**
     * Add alfaname
     *
     * @param string $alfaname
     *
     * @return mixed
     */
    public function addAlfaname($alfaname)
    {
        $xml = $this->startXml();
        $xml .= '<request><operation>MANAGEALFANAME</operation><command id="ADDALFANAME" alfaname="' . $alfaname . '" /></request>';

        return $this->sendRequest($xml, 'addAlfaname');
    }

    /**
     * Check alfaname
     *
     * @param string $alfaname
     *
     * @return mixed
     */
    public function checkAlfaname($alfaname)
    {
        $xml = $this->startXml();
        $xml .= '<request><operation>MANAGEALFANAME</operation><command id="CHECKALFANAME" alfaname="' . $alfaname . '" /></request>';

        return $this->sendRequest($xml, 'checkAlfaname');
    }

    /**
     * @return mixed
     */
    public function getAlfanamesList()
    {
        $xml = $this->startXml();
        $xml .= '<request><operation>MANAGEALFANAME</operation><command id="GETALFANAMESLIST" /></request>';

        return $this->sendRequest($xml, 'alfanamesList');
    }

    /**
     * @param string $data
     * @param string $parseMethod
     * @return mixed
     */
    private function sendRequest($data, $parseMethod)
    {
        $response = $this->request($data);

        if ($response) {
            return $this->parse($response, $parseMethod);
        }

        return false;
    }

    /**
     * Request (POST)
     *
     * @param string $data
     * @return bool|string
     */
    private function request($data)
    {
        $auth = $this->login . ':' . $this->password;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml',
            'Accept: text/xml',
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response) {
            return $response;
        }

        return false;
    }

    /**
     * Parse XML
     *
     * @param string $data
     * @param string $method
     * @return mixed
     */
    private function parse($data, $method)
    {
        if ($data === 'Access denied! Incorrect login or password.') {
            return $this->messages['DENIED'];
        }

        try {
            $this->parseXML->setXML($data);

            return $this->parseXML->$method();
        } catch (Exception $e) {
            error_log($data);

            return false;
        }
    }

    /**
     * Format phone number
     *
     * @param string $phoneNumber
     * @return string
     */
    public function formatPhoneNumber($phoneNumber)
    {
        return preg_replace('/[^0-9+]/', '', $phoneNumber);
    }

    /**
     * @return string
     */
    private function startXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
    }
}
