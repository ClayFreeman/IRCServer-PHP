<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "NickChangeEvent",
      "UserRegistrationEvent");
    public $name = "NICK";

    private function nicknameAvailable($nick) {
      foreach (ConnectionManagement::getConnections() as $c) {
        if (strtolower($nick) == $c->getOption("nick")) {
          return false;
        }
      }
      return true;
    }

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "nick") {
        if (preg_match("/^[[\\]a-zA-Z\\\\`_^{|}][[\\]a-zA-Z0-9\\\\`_^{|}-]*$/",
            $command[1]) && count($command) == 2) {
          if ($this->nicknameAvailable(substr($command[1], 0, 30)) != false) {
            $oldnick = $connection->getOption("nick");
            $connection->setOption("nick", substr($command[1], 0, 30));
            if ($connection->getOption("ident") != false &&
                $connection->getOption("registered") == false) {
              $connection->setOption("registered", true);
              $event = EventHandling::getEventByName("userRegistrationEvent");
              if ($event != false) {
                foreach ($event[2] as $id => $registration) {
                  // Trigger the userRegistrationEvent event for each registered
                  // module.
                  EventHandling::triggerEvent("userRegistrationEvent", $id,
                      $connection);
                }
              }
            }
            else {
              $event = EventHandling::getEventByName("nickChangeEvent");
              if ($event != false) {
                foreach ($event[2] as $id => $registration) {
                  // Trigger the nickChangeEvent event for each registered
                  // module.
                  EventHandling::triggerEvent("nickChangeEvent", $id,
                      array($connection, $oldnick));
                }
              }
            }
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 433 ".(
              $connection->getOption("nick") ? $connection->getOption("nick") :
              "*")." ".$command[1]." :Nickname is already in use.");
          }
        }
        elseif (count($command) > 2) {
          $connection->send(":".__SERVERDOMAIN__." 432 * ".$command[1].
            " :Erroneous Nickname");
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 431 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :No nickname given");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
