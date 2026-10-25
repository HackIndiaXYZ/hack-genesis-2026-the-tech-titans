<?php
// ---- Settings (edit these) ----
define('DB_HOST','localhost'); define('DB_USER','root'); define('DB_PASS',''); define('DB_NAME','highway_assist');
define('AI_API_KEY','');            // optional: Anthropic API key for smarter AI replies. Empty = built-in smart assistant
define('AI_MODEL','claude-sonnet-4-6');
// --------------------------------
date_default_timezone_set('Asia/Kolkata');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
$root=str_replace('\\','/',realpath($_SERVER['DOCUMENT_ROOT'])); $dir=str_replace('\\','/',__DIR__);
define('BASE',rtrim(substr($dir,strlen($root)),'/').'/');
mysqli_report(MYSQLI_REPORT_OFF);
$db=@new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
if($db->connect_errno&&!defined('INSTALLING'))die(json_encode(['ok'=>0,'err'=>'Database connect nahi hua. install.php chalayein aur config.php check karein.']));
if(!$db->connect_errno){$db->set_charset('utf8mb4');$db->query("SET time_zone='+05:30'");}
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function q($sql,$t='',...$p){global $db;$s=$db->prepare($sql);if(!$s)die('SQL error: '.e($db->error));if($t)$s->bind_param($t,...$p);$s->execute();$r=$s->get_result();return $r===false?$db->insert_id:$r->fetch_all(MYSQLI_ASSOC);}
function me(){static $u=null;if(!isset($_SESSION['uid']))return null;if(!$u){$r=q("SELECT * FROM users WHERE id=?",'i',$_SESSION['uid']);$u=$r[0]??null;}return $u;}
function need($role){$u=me();if(!$u||$u['role']!=$role||$u['status']=='blocked'){header('Location: '.BASE.'login.php?role='.$role);exit;}return $u;}
