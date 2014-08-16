<?php
  class @@CLASSNAME@@ {
    public $depend = array("WHOISResponseEvent");
    public $name = "SSLInfoWHOISResponse";

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      if ($target->getSSL() == true) {
        $weight = 42;
        if (!isset($response[$weight])) {
          $response[$weight] = array();
        }
        $response[$weight][] = ":".__SERVERDOMAIN__." 671 ".
          $source->getOption("nick")." ".$target->getOption("nick")." :is ".
          "using a secure connection";
        $data[2] = $response;
        return array(null, $data);
      }
      return array(true);
    }

    public function isInstantiated() {
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
