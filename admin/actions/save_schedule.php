<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/db.php'; requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid request.'); }
$center=filter_var(requestScalar($_POST,'center_id'),FILTER_VALIDATE_INT); $date=requestDate($_POST,'schedule_date'); $time=requestEnum($_POST,'schedule_time',['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00']); $type=requestEnum($_POST,'dialysis_type',['Hemodialysis','Peritoneal Dialysis','Home Hemodialysis','Continuous Ambulatory Peritoneal Dialysis','Automated Peritoneal Dialysis']); $slots=filter_var(requestScalar($_POST,'available_slots'),FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
if(!$center||!$date||$time===''||$type===''||$slots===false||$date->format('Y-m-d')<date('Y-m-d')){header('Location: ../schedules.php?message=Please enter valid schedule details.');exit;}
$stmt=$adminConnection->prepare('INSERT INTO center_schedules (center_id,schedule_date,schedule_time,dialysis_type,available_slots) VALUES (:center,:date,:time,:type,:slots)');$stmt->execute(['center'=>$center,'date'=>$date->format('Y-m-d'),'time'=>$time,'type'=>$type,'slots'=>$slots]);header('Location: ../schedules.php?message=Schedule added successfully.');exit;
