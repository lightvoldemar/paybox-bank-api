<?php
namespace PayboxBankApi;

use yii\helpers\Json;
use yii\httpclient\Client;
use Yii;
use common\models\Orders;
use common\models\OrderResult;



/**
 * Клиентский класс.
 */
class PayboxClient
{
    const CARDS = 'EPAYWEBKZT';
    /**
     * Ярлык Kassa24 сервиса.
     */
    const KASSA = 'KASSA24';

    /**
     * Ярлык KassaNova банка.
     */
    const KASSANOVA = 'KASSANOVA';

    /**
     * Ярлык Казпочты.
     */
    const KAZPOST = 'KAZPOSTKZT';

    /**
     * Ярлык ATF банка.
     */
    const ATF24   = 'ATF24KZT';

    /**
     * Ярлык Halyk банка.
     */
    const HOMEBANK = 'HOMEBANKKZT';

    /**
     * Ярлык Forte банка.
     */
    const FORTEBANK = 'FORTEBANKKZT';

    /**
     * Ярлык Alfa банка.
     */
    const ALFACLICK = 'ALFACLICKKZT';

    /**
     * Ярлык RBK банка.
     */
    const BANKRBK24 = 'BANKRBK24KZT';

    /**
     * Ярлык Qiwi банка.
     */
    const QIWIWALLET = 'QIWIWALLETKZT';

    /**
     * Ярлык Smart банка.
     */
    const SMARTBANK = 'SMARTBANKKZT';

    /**
     * Ярлык Сбербанк банка.
     */
    const SBERBANK  = 'SBERONLINEKZT';

    const PAYMENT_PAYBOX = 1;
    const PAYMENT_KASSA_N = 2;
    const DEFAULT_PERCENT = 3;
    const KASSA_PERCENT = 2.9;
    const QIWI_PERCENT = 3.7;
    const LIFE_TIME_SEC = 1800; //30min

    const PURSE_PILLIKAN = 1;
    const PURSE_CASHBACK = 2;

    public static $isPaybox = true;

    private $params;

    /**
     * URL редиректа.
     */
    public $dataRedirectUrl = "ERROR";

    /**
     * Код платежа.
     */
//    public $dataOrderSig;

    /**
     * URL редиректа пользователя.
     */
    public $redirectUrl;

    /**
     * URL успешной транзакции.
     */
    public $returnUrl;

    /**
     * URL не успешной транзакции.
     */
    public $failUrl;

    /**
     * API логин.
     */
    public $apiLogin;

    /**
     * API пароль.
     */
    public $apiPassword;

    /**
     * Ошибки.
     */
    public $errorMessage;

    /**
     * URL оплаты.
     */
    public $payUrl = 'https://api.paybox.money/payment.php';

    /**
     * Соль.
     */
    public $paySalt;

    /**
     * Генерация SIG.
     */
    public $sig;

    /**
     * Текущая валюта.
     */
    private $currency;
    
    /**
     * Доступные валюты (ISO 4217).
     *
     * @var array
     */
    protected $currencyEnum = array(

    );

    public function __construct($id, $paymentType, $amount, $salt)
    {
        $this->salt = $this->generateSalt();
        $this->payId = $id;
        $this->payPaymentType = $paymentType;
        $this->payAmount = $amount;
        $this->paySalt = $salt;

        $this->params = Yii::$app->params['pay']['paybox']['merchantId'];

        $this->generateSig($id, $salt, $paymentType, $amount);
    }

    /**
     * Генерация соли.
     *
     * @return object
     */
    public static function generateSalt() {
        $keySpace = '0123456789ABCDEFGHIJKLMNOPQRSTVabcodeakjnvpoawiaowid';
        $str = '';
        $max = mb_strlen($keySpace, '8bit') - 1;
        for ($i = 0; $i < 10; ++$i) {
            $str .= $keySpace[mt_rand(0, $max)];
        }
        return $str;
    }

    /**
     * Генерация строки.
     *
     * @return object
     */
    public function generateSig() {
        return $this->sig = md5('payment.php;'.$this->payAmount.';KZT;За услуги рекламы;'
            .self::LIFE_TIME_SEC.';'
            .$this->params['merchantId']. ';'
            .$this->payId.';'.$this->payPaymentType.';'
            .$this->salt.';'.$this->params['secretKey']);
    }
    
    /**
     * Генерация ссылки.
     *
     * @return object
     */
    public function generateLink($purseId) {
        return $this->redirectUrl = $this->payUrl.'?'
            .'pg_lifetime='.self::LIFE_TIME_SEC
            .'&pg_merchant_id='.$this->params['merchantId']
            .'&pg_order_id='.$this->payId
            .'&pg_amount='.$this->payAmount
            .'&pg_currency='.ArrayHelper::getValue(static::getCurrency(), $this->params['currency'])
            .'&pg_description='.ArrayHelper::getValue(static::getPurseDescription(), $purseId)
            .'&pg_payment_system='.$this->payPaymentType
            .'&pg_salt='.$this->paySalt
            .'&pg_sig='.$this->sig
            .'&pg_success_url='.$this->returnUrl
            .'&pg_failure_url='.$this->failUrl;
    }

    public static function getPurseDescription() {
        return [
            self::PURSE_PILLIKAN => Yii::t('app', 'Advertising Services'),
            self::PURSE_CASHBACK => Yii::t('app', 'Cashback Services'),
        ];
    }

    public static function getCurrency() {
        return [
            840 => 'USD',
            398 => 'KZT',
        ];
    }
  
}
