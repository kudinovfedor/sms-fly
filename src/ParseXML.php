<?php

namespace KudinovFedor\SmsFly;

use SimpleXMLElement;

/**
 * Class ParseXml
 * @package KudinovFedor\SmsFly
 * @author Kudinov Fedor <admin@joompress.biz>
 * @license https://opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/kudinovfedor/sms-fly
 */
class ParseXML
{
    /**
     * @var string
     */
    private $data;

    /**
     * @var SimpleXMLElement
     */
    private $xml;

    /**
     * Statuses
     *
     * @var array
     */
    private $statusCode = [
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
     * Set XML
     *
     * @param string $data
     * @return ParseXml
     */
    public function setXML($data)
    {
        $this->data = $data;
        $this->xml = new SimpleXMLElement($data);

        return $this;
    }

    /**
     * Send sms
     *
     * @return string
     */
    public function sendSMS()
    {
        $code = (string)$this->xml->state['code'];

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
                error_log($this->data);
                return false;
        }

        return $text;
    }

    /**
     * Get balance value
     *
     * @return string
     */
    public function balance()
    {
        return (string)$this->xml->balance;
    }

    /**
     * Get campaign info
     *
     * @return array
     */
    public function campaignInfo()
    {
        $campaign = $this->processCampaign();
        $state = $this->processCampaignState();

        return array_merge($campaign, $state);
    }

    /**
     * Get campaign detail
     *
     * @return array
     */
    public function campaignDetail()
    {
        $campaign = $this->processCampaign();
        $message = $this->processMessage($this->xml->campaign->message);

        return array_merge($campaign, $message);
    }

    /**
     * Message status
     *
     * @return array
     */
    public function messageStatus()
    {
        return $this->processState();
    }

    /**
     * Add alfaname
     *
     * @return array
     */
    public function addAlfaname()
    {
        return $this->processState();
    }

    /**
     * Check alfaname
     *
     * @return array
     */
    public function checkAlfaname()
    {
        return $this->processState();
    }

    /**
     * Alfanames list
     *
     * @return array
     */
    public function alfanamesList()
    {
        return $this->processState();
    }

    /**
     * Process campaign
     *
     * @return array
     */
    private function processCampaign()
    {
        $data = [];

        foreach ($this->xml->campaign->attributes() as $attribute => $value) {
            $data[$attribute] = $value->__toString();
        }

        return $data;
    }

    /**
     * Process campaign
     *
     * @return array
     */
    private function processCampaignState()
    {
        $data = [];
        $index = 0;

        foreach ($this->xml->campaign->state as $item) {
            $key = null;
            $val = null;

            foreach ($item->attributes() as $attribute => $value) {
                if ($attribute === 'status') $key = $value->__toString();
                if ($attribute === 'messages') $val = $value->__toString();
            }

            $data['state'][$key] = $val;

            $index++;
        }

        return $data;
    }

    /**
     * Process message
     *
     * @param SimpleXMLElement $message
     * @return array
     */
    private function processMessage($message)
    {
        $data = [];
        $index = 0;

        foreach ($message as $item) {
            foreach ($item->attributes() as $attribute => $value) {
                $data['message'][$index][$attribute] = $value->__toString();
            }

            $index++;
        }

        return $data;
    }

    /**
     * Process state
     *
     * @param SimpleXMLElement $state
     * @return array
     */
    private function processState($state = null)
    {
        $data = [];
        $index = 0;
        $state = is_null($state) ? $this->xml->state : $state;

        foreach ($state as $item) {
            foreach ($item->attributes() as $attribute => $value) {
                $data[$index][$attribute] = $value->__toString();
            }

            $index++;
        }

        if ($state->count() === 1) return $data[0];

        return $data;
    }
}
