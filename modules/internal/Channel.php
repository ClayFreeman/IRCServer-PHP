<?php
  class @@CLASSNAME@@ {
    public $depend = array("ChannelJoinEvent", "ChannelMessageEvent",
      "NickChangeEvent", "UserQuitEvent");
    public $name = "Channel";
    private $options = array();

    public function broadcast($name, $data, $exclude = null) {
      if (!is_array($exclude)) {
        $exclude = array($exclude);
      }
      $channels = $source->getOption("channels");
      if ($channels == false) {
        return;
      }
      foreach ($channels as $channel) {
        if (strtolower($channel["name"]) == strtolower($name)) {
          foreach ($channel["members"] as $id) {
            if (!in_array($id, $exclude)) {
              foreach (ConnectionManagement::getConnections() as $connection) {
                if ($connection->getOption("id") == $id) {
                  $connection->send($data);
                }
              }
            }
          }
        }
      }
    }

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false.
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function receiveChannelJoin($name, $data) {

    }

    public function receiveChannelMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost()." PRIVMSG ".$target->getOption("name")." :";

      if (strlen($base.$message) > 510) {
        $chunks = str_split($message, (510 - strlen($base)));
        foreach ($chunks as $chunk) {
          $this->broadcast($target, $base.$chunk, $source->getOption("id"));
        }
      }
      else {
        $this->broadcast($target, $base.$message, $source->getOption("id"));
      }
    }

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];
      $channels = $source->getOption("channels");
      if ($channels == false) {
        return;
      }

      $targets = array();
      foreach ($channels as $channel) {
        if ($this->getOption("channels") != false) {
          foreach ($this->getOption("channels") as &$ch) {
            if (strtolower($ch["name"]) == strtolower($channel)) {
              $targets = array_values(array_unique(array_merge(
                array_values($targets), array_values($ch["members"]))));
            }
          }
        }
      }

      foreach ($targets as $target) {
        foreach (ConnectionManagement::getConnections() as $t) {
          if ($t->getOption("id") == $target) {
            $t->send(":".$oldnick."!".$source->getOption("ident").
              "@".$source->getHost()." NICK :".$source->getOption("nick"));
          }
        }
      }
    }

    public function setOption($key, $value) {
      // Set an option for this connection.
      $this->options[$key] = $value;
      return true;
    }

    public function receiveUserQuit($name, $data) {
      $source = $data[0];
      $message = $data[1];
      $channels = $source->getOption("channels");
      if ($channels == false) {
        return;
      }

      $targets = array();
      foreach ($channels as $channel) {
        if ($this->getOption("channels") != false) {
          foreach ($this->getOption("channels") as &$ch) {
            if (strtolower($ch["name"]) == strtolower($channel)) {
              $targets = array_values(array_unique(array_merge(
                array_values($targets), array_values($ch["members"]))));
            }
          }
        }
      }

      foreach ($targets as $target) {
        foreach (ConnectionManagement::getConnections() as $t) {
          if ($t->getOption("id") == $target) {
            $t->send(":".$source->getOption("nick")."!".
              $source->getOption("ident")."@".$source->getHost()." QUIT :".
              $message);
          }
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerForEvent("channelMessageEvent", $this,
        "receiveChannelMessage");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      return true;
    }
  }
?>