<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelModeEvent",
      "LackOfChannelOperatorShouldPreventInvitationEvent", "Modes");
    public $name = "UnrestrictedInvite";
    private $channel = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"],
        array("UnrestrictedInvite"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "UnrestrictedInvite") {
          if ($mode["operation"] == "+") {
            if ($has != false) {
              unset($modes[$key]);
            }
            else {
              $has = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($has == false) {
              unset($modes[$key]);
            }
            else {
              $has = false;
            }
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveLackOfChannelOperatorShouldPreventInvitation($name,
        $data) {
      $source = $data[0];
      $target = $data[1];
      $channel = $data[2];

      if (is_array($channel)) {
        $channel = $channel["name"];
      }

      $modes = $this->channel->hasModes($channel,
        array("UnrestrictedInvite"));
      if ($modes != false) {
        // Ban is exempted.
        return false;
      }

      // Ban is not exempted.
      return true;
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("UnrestrictedInvite", "g", "0", "0"));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerForEvent(
        "lackOfChannelOperatorShouldPreventInvitationEvent", $this,
        "receiveLackOfChannelOperatorShouldPreventInvitation");
      return true;
    }
  }
?>
