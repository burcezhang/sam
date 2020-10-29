<?php
class MQTTMessage {
	public $body='';
	public function MQTTMessage($body='') {
		if ($body != '') {
			$this->body = $body;
		}
	}
}
?>