<?php

namespace KudinovFedor\SmsFly;

use Exception;
use SimpleXMLElement;

/**
 * Class SmsFly
 * @author Kudinov Fedor <admin@joompress.biz>
 * @license https://opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/kudinovfedor/sms-fly
 */
class SmsFly
{
    const VERSION = '0.0.1';

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
     * Sender. Specifies an alphanumeric name (alpha name).
     *
     * @description Only alphanumeric names registered for the user are allowed.
     *
     * @var string
     */
    private $source = 'InfoCentr';

    /**
     * Statuses
     *
     * @var array
     */
    private $statusCode = [
        'DENIED' => 'Неверные учетные данные!',
        'ACCEPT' => 'Сообщение принято системой и поставлено в очередь на формирование рассылки.',
        'XMLERROR' => 'Некорректный XML .',
        'ERRPHONES' => 'Неверно задан номер получателя.',
        'ERRSTARTTIME' => 'Не корректное время начала отправки.',
        'ERRENDTIME' => 'Не корректное время окончания рассылки.',
        'ERRLIFETIME' => 'Не корректное время жизни сообщения.',
        'ERRSPEED' => 'Не корректная скорость отправки сообщений.',
        'ERRALFANAME' => 'Дданное альфанумерическое имя использовать запрещено, либо ошибка .',
        'ERRTEXT' => 'Некорректный текст сообщения.',
        'INSUFFICIENTFUNDS' => 'Недостаточно средств. Проверяется только при получении запроса на отправку СМС сообщения одному абоненту.',
    ];

    /**
     * SmsFly constructor.
     *
     * @param array $args
     */
    public function __construct($args = [])
    {
        if ($args['login']) $this->login = $args['login'];
        if ($args['password']) $this->password = $args['password'];
        if ($args['source']) $this->source = htmlspecialchars($args['source']);
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
     * Set source
     *
     * @param string $source
     * @return SmsFly
     */
    public function setSource($source)
    {
        $this->source = htmlspecialchars($source);

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
     * Set recipient
     *
     * @param string|array $recipient
     * @return SmsFly
     */
    public function setRecipient($recipient)
    {
        if (is_array($recipient)) {

            $recipient = array_map(function ($phone) {
                return preg_replace('/[^0-9+]/', '', $phone);
            }, $recipient);

        } else {

            $recipient = preg_replace('/[^0-9+]/', '', $recipient);

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
     * Set status code
     *
     * @param array $statusCode
     * @return SmsFly
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Send SMS
     *
     * @return mixed
     */
    public function sendSms()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
        $xml .= '<request>';
        $xml .= '<operation>SENDSMS</operation>';
        $xml .= sprintf(
            '<message start_time="%s" end_time="%s" lifetime="%d" rate="%d" desc="%s" source="%s" version="%s">' . PHP_EOL,
            $this->startTime, $this->endTime, $this->lifeTime, $this->rate, $this->description, $this->source, self::VERSION
        );
        $xml .= '<body>' . $this->message . '</body>';

        if (is_array($this->recipient)) {
            foreach ($this->recipient as $phone) {
                $xml .= '<recipient>' . $phone . '</recipient>';
            }
        } else {
            $xml .= '<recipient>' . $this->recipient . '</recipient>';
        }

        $xml .= '</message></request>';

        $response = $this->request($xml);

        if ($response) return $this->parse($response, 'code');

        return false;
    }

    /**
     * Get balance
     *
     * @return mixed
     */
    public function getBalance()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
        $xml .= '<request><operation>GETBALANCE</operation></request>';

        $response = $this->request($xml);

        if ($response) return $this->parse($response, 'balance');

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

        if ($response) return $response;

        return false;
    }

    /**
     * Parse XML
     *
     * @param string $data
     * @param string $child
     * @return mixed
     */
    private function parse($data, $child)
    {
        if ($data === 'Access denied! Incorrect login or password.') {
            return $this->statusCode['DENIED'];
        }

        try {
            $xml = new SimpleXMLElement($data);

            if ($child === 'code') {

                $code = (string)$xml->state['code'];

                switch ($code) {
                    case 'ACCEPT':
                        $text = $this->statusCode['ACCEPT'];
                        break;
                    case 'XMLERROR':
                        $text = $this->statusCode['XMLERROR'];
                        break;
                    case 'ERRPHONES':
                        $text = $this->statusCode['ERRPHONES'];
                        break;
                    case 'ERRSTARTTIME':
                        $text = $this->statusCode['ERRSTARTTIME'];
                        break;
                    case 'ERRENDTIME':
                        $text = $this->statusCode['ERRENDTIME'];
                        break;
                    case 'ERRLIFETIME':
                        $text = $this->statusCode['ERRLIFETIME'];
                        break;
                    case 'ERRSPEED':
                        $text = $this->statusCode['ERRSPEED'];
                        break;
                    case 'ERRALFANAME':
                        $text = $this->statusCode['ERRALFANAME'];
                        break;
                    case 'ERRTEXT':
                        $text = $this->statusCode['ERRTEXT'];
                        break;
                    case 'INSUFFICIENTFUNDS':
                        $text = $this->statusCode['INSUFFICIENTFUNDS'];
                        break;
                    default:
                        error_log($data);
                        return false;
                }

                return $text;

            } elseif ($child === 'balance') {

                return (string)$xml->balance;

            }

        } catch (Exception $e) {
            error_log($data);

            return false;
        }
    }
}
