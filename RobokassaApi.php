<?php
/**
 * User: Alexok
 * Date: 25.10.13
 * Time: 10:21
 */

class RobokassaApi 
{
	public $params;

	function __construct()
	{
		$this->params = Yii::app()->ini->get('robokassa');

		if (!$this->params)
			throw new CException('Robokassa params not defined');
	}

	/**
	 * Create link to be payment
	 * @param $price
	 * @param $orderId
	 * @return string
	 */
	public function getPaymentUrl($price, $orderId)
	{
		$url = $this->params['url'];

		$params = array(
			'MrchLogin' => $this->params['login'],
			'OutSum' => $price,
			'InvId' => $orderId,
			'Desc' => Yii::t('shop', 'Payment order {orderId}', array('{orderId}'=>$orderId)),
			'SignatureValue' => $this->generateSignature($price, $orderId),
			'Culture' => 'ru',
			'Encoding' => 'utf-8',
		);

		return $url.'?'.http_build_query($params);
	}

	/**
	 * Verify a CRC value
	 * @return bool
	 */
	public function verifySignature()
	{
		$signatureValue = Yii::app()->request->getPost('SignatureValue');

		$params = array(
			$this->getVerifiablePrice(),
			$this->getVerifiableOrderId(),
			$this->params['password2']
		);

		return strtolower($signatureValue) === md5(implode(':', $params));
	}

	public function getVerifiableOrderId()
	{
		return (int) Yii::app()->request->getPost('InvId');
	}

	public function getVerifiablePrice()
	{
		return Yii::app()->request->getPost('OutSum');
	}

	/**
	 * Create a CRC value
	 * @param $price
	 * @param $orderId
	 * @return string
	 */
	private function generateSignature($price, $orderId)
	{
		$params = array(
			$this->params['login'],
			$price,
			$orderId,
			$this->params['password1']
		);

		return md5(implode(':', $params));
	}
}
