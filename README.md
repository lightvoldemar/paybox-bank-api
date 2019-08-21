# Paybox Банк API
PHP library for Paybox integration

## Установка
```
$ composer require lightvoldemar/paybox-bank-api
```
## Пример
```
// Сумма платежа
$amount; 
// ID ордера
$orderId; 
```
```
// Создание объекта Paybox с параметрами ID ордера, тип платежа, сумма транзакции, соль.
$oPayboxPay = new PayboxClient($order->id,$order->payment_type, $order->amount, $order->salt);
// Генератор соли
PayboxClient::generateSalt();
// Генератор строки
$oPayboxPay->generateSig();
// Генератор ссылки с параметром типа счета
$oPayboxPay->generateLink(PayboxClient::PURSE_PILLIKAN)

```
```
