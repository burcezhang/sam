<?php
include_once('AnyCareMQTT.php');

class MQQTTConnection {
//  var $debug = true;
  var $debug = false;

  var $errno = 0;
  var $error = '';

  var $connection;
	function _e($s) {print '-->'.$s.PHP_EOL;}
	function _t($s) {print '   '.$s.PHP_EOL;}
	function _x($s) {print '<--'.$s.PHP_EOL;}

  /* ---------------------------------
      Create
     --------------------------------- */
  function Create($proto) {
      if ($this->debug) $this->_e("MQQTTConnection.Create(proto=$proto)");
      $rc = false;
      /* search the PHP config for a factory to use...    */
      $x = get_cfg_var('mqtt.factory.'.$proto);
      $rc = new AnyCareMQTT();

      if ($this->debug && $rc) _t('MQQTTConnection.Create() rc = '.get_class($rc));
      if ($this->debug) $this->_x('MQQTTConnection.Create()');
      return $rc;
  }

  /* ---------------------------------
      Constructor
     --------------------------------- */
  function MQQTTConnection() {
    if ($this->debug) $this->_e('MQQTTConnection()');

    if ($this->debug) $this->_x('MQQTTConnection()');
  }

  /* ---------------------------------
      Commit
     --------------------------------- */
  function Commit() {
    if ($this->debug) $this->_e('MQQTTConnection.Commit()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->commit($target, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Commit() commit failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Commit() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Connect
     --------------------------------- */
  function Connect($proto='', $options=array()) {
    if ($this->debug) $this->_e('MQQTTConnection.Connect()');
    $rc = false;

    if ($proto == '') {
        $errno = 101;
        $error = 'Incorrect number of parameters on connect call!';
        $rc = false;
    } else {
        $this->connection = $this->create($proto);
        if (!$this->connection) {
            $errno = 102;
            $error = 'Unsupported protocol!';
            $rc = false;
        } else {
            if ($this->debug) $this->_t("MQQTTConnection.Connect() connection created for protocol $proto");

            $this->connection->setdebug($this->debug);

            /* Call the connect method on the newly created connection object...   */
            $rc = $this->connection->connect($proto, $options);
            $this->errno = $this->connection->errno;
            $this->error = $this->connection->error;
            if (!$rc) {
               if ($this->debug) $this->_t("MQQTTConnection.Connect() connect failed ($this->errno) $this->error");
            } else {
               $rc = true;
            }
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Connect() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Disconnect
     --------------------------------- */
  function Disconnect() {
    if ($this->debug) $this->_e('MQQTTConnection.Disconnect()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->Disconnect();
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Disconnect() Disconnect failed ($this->errno) $this->error");
        } else {
            $rc = true;
            $this->connection = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Disconnect() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      IsConnected
     --------------------------------- */
  function IsConnected() {
    if ($this->debug) $this->_e('MQQTTConnection.IsConnected()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->isconnected();
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.IsConnected() isconnected failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.IsConnected() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Peek
     --------------------------------- */
  function Peek($target, $options=array()) {
    if ($this->debug) $this->_e('MQQTTConnection.Peek()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->peek($target, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Peek() peek failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Peek() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      PeekAll
     --------------------------------- */
  function PeekAll($target, $options=array()) {
    if ($this->debug) $this->_e('MQQTTConnection.PeekAll()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->peekall($target, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.PeekAll() peekall failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.PeekAll() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Receive
     --------------------------------- */
  function Receive($target, $options=array()) {
    if ($this->debug) $this->_e('MQQTTConnection.Receive()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the receive method on the underlying connection object...   */
        $rc = $this->connection->receive($target, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Receive() receive failed ($this->errno) $this->error");
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Receive() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Remove
     --------------------------------- */
  function Remove($target, $options=array()) {
    if ($this->debug) $this->_e('MQQTTConnection.Remove()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->remove($target, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Remove() remove failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Remove() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Rollback
     --------------------------------- */
  function Rollback() {
    if ($this->debug) $this->_e('MQQTTConnection.Rollback()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the method on the underlying connection object...   */
        $rc = $this->connection->rollback($target, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Rollback() rollback failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Rollback() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Send
     --------------------------------- */
  function Send($target, $msg, $options=array()) {
    if ($this->debug) $this->_e('MQQTTConnection.Send()');
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the send method on the underlying connection object...   */
        $rc = $this->connection->send($target, $msg, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Send() send failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Send() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      SetDebug
     --------------------------------- */
  function SetDebug($option=false) {
    if ($this->debug) $this->_e("MQQTTConnection.SetDebug($option)");

    $this->debug = $option;

    if ($this->connection) {
        $this->connection->setdebug($option);
    }

    if ($this->debug) $this->_x('MQQTTConnection.SetDebug()');
    return;
  }

  /* ---------------------------------
      Subscribe
     --------------------------------- */
  function Subscribe($topic, $options=array()) {
    if ($this->debug) $this->_e("MQQTTConnection.Subscribe($topic)");
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the subscribe method on the underlying connection object...   */
        $rc = $this->connection->subscribe($topic, $options);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Subscribe() subscribe failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Subscribe() rc=$rc");
    return $rc;
  }

  /* ---------------------------------
      Unsubscribe
     --------------------------------- */
  function Unsubscribe($sub_id) {
    if ($this->debug) $this->_e("MQQTTConnection.Unsubscribe($sub_id)");
    $rc = true;

    if (!$this->connection) {
        $errno = 106;
        $error = 'No active connection!';
        $rc = false;
    } else {
        /* Call the subscribe method on the underlying connection object...   */
        $rc = $this->connection->unsubscribe($sub_id);
        $this->errno = $this->connection->errno;
        $this->error = $this->connection->error;
        if (!$rc) {
            if ($this->debug) $this->_t("MQQTTConnection.Unsubscribe() unsubscribe failed ($this->errno) $this->error");
            $rc = false;
        }
    }

    if ($this->debug) $this->_x("MQQTTConnection.Unsubscribe() rc=$rc");
    return $rc;
  }
}
?>