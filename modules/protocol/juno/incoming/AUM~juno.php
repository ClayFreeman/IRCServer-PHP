<?php
  class __CLASSNAME__ {
    public $depend = array("Modes", "Outgoing~juno");
    public $name = "AUM~juno";
    private $juno = null;
    private $modes = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      $map = (is_array($connection->getOption("umodemap")) ?
        $connection->getOption("umodemap") : array());
      if (count($command) > 0) {
        foreach ($command as $item) {
          $item = explode(":", $item);
          $localName = $this->juno->convertIncomingUModeName($item[0]);
          if ($localName != false) {
            $map[$item[1]] = $this->modes->getModeByName($localName);
          }
          else {
            $map[$item[1]] = array($item[0], $item[1], "1", "0");
          }
        }
      }
      $connection->setOption("umodemap", $map);
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Outgoing~juno");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("aum", true, "juno"));
      return true;
    }
  }
?>