<?php
/**
 * Created by SyncroBit.
 * Author: George Partene
 * Version: 0.1
 */

define("SB_CORE", dirname(dirname(__FILE__))."/");
define("SB_LIBS", SB_CORE."libs/");

define("SNAPSHOT_DIR", "/home/syncrobit/html/snapshots/");
define("SNAPSHOT_URI", "https://".$_SERVER['HTTP_HOST']."/snapshots/");
define("MINER_DATA", "/home/syncrobit/miner_data/");

/** MySQL Credentials */
define("SB_DB_HOST", "mysql.local");
define("SB_DB_USER", "syncrobit");
define("SB_DB_PASSWORD", "m3rt3c123");
define("SB_DB_DATABASE", "syncrobit");