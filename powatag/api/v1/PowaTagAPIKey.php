<?php

class PowaTagAPIKey {

	public function verifyKey($key, $data)
	{
		return $this->buildHmac($data) == $key;
	}

	public function buildHmac($data)
	{
		$hmac_key = Configuration::get(Tools::strtoupper('powatag_hmac_key'));
		$calculateHmac = base64_encode(hash('sha256', $hmac_key.$data, true));

		return $calculateHmac;
	}

}

?>