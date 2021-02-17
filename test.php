<?php
include "includes/initd.inc.php";

$files = scandir(SNAPSHOT_DIR);
        $now   = time();
      
        foreach ($files as $file) {
          if (is_file(SNAPSHOT_DIR.$file)) {
              echo filemtime(SNAPSHOT_DIR.$file);
            if ($now - filemtime(SNAPSHOT_DIR.$file) >= 60 * 30) { // 30 minutes
                //unlink(SNAPSHOT_DIR.$file);
            }
          }
        }