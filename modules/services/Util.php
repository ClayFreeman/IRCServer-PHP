<?php
  class @@CLASSNAME@@ {
    public $depend = array();
    public $name = "Util";

    public function prettyStrChunk($string, $size, $ending) {
      $ret = array();
      $s = explode("\n", $string);
      foreach ($s as &$string) {
        $string = explode(" ", trim($string));
      }
      Logger::info(var_export($s, true));
      $line = 0;
      foreach ($s as $string) {
        Logger::info(var_export($s, true));
        while (count($string) > 0) {
          $line++;
          $ret[$line] = null;
          $lastCount = count($string);
          while (isset($string[0]) && (strlen($ret[$line]) +
                  (strlen($string[0]) + (strlen($ending) + 2))) < ($size + 1)) {
            $ret[$line] .= " ".array_shift($string);
            $ret[$line] = trim($ret[$line]);
          }
          if (count($string) == $lastCount) {
            if (strlen($string[0]) > ($size - strlen($ending))) {
              $string = chunk_split(array_shift($string),
                ($size - (strlen($ending) + 1)), "-".$ending);
            }
          }
        }
      }
      Logger::info(var_export($ret, true));
      return implode("\r\n", $ret);
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
