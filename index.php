<?php

require('vendor/autoload.php');

use IntelliTrend\Zabbix\ZabbixApi;

include_once './config.php';

$zabbixApiOptions = array('sslVerifyPeer' => true, 'sslVerifyHost' => true);
$zabbixApi = new ZabbixApi();

try {
	$zabbixApi->loginToken($zabbixApiServerUri, $zabbixApiAccessToken, $zabbixApiOptions);
	$zabbixServices = $zabbixApi->call('service.get', array("output" => "extend", "sortfield" => "sortorder"));
} catch (Exception $ex) {
	print "==== Exception ===\n";
	print 'ErrorCode: '.$ex->getCode()."\n";
	print 'ErrorMessage: '.$ex->getMessage()."\n";
	exit;
}

function getColBlockStyleForIndex($index) {
  if ($index < 7) {
    return 'd-none d-xl-block';
  } else if ($index < 14) {
    return 'd-none d-lg-block';
  } else if ($index < 21) {
    return 'd-none d-md-block';
  } else {
    return 'd-block';
  }
}

function getColBlockColorFor($sliValue, $sloValue) {
  if ($sliValue < $sloValue) {
    return 'bg-warning';
  } else {
    return 'bg-success';
  }
}

?>
<html>
  <head>
    <link href="https://assets.x3cdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://assets.x3cdn.com/bootstrap-icons/1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      main > .container {
        padding-top: 60px;
      }
    </style>
    <script src="https://assets.x3cdn.com/bootstrap/5.3.0/js/bootstrap.bundle.js"></script>
    <script>
      function onDomContentLoaded(event) {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
      }
      document.addEventListener("DOMContentLoaded", onDomContentLoaded);
    </script>
    <meta charset="UTF-8">
    <title>Status</title>
  </head>
  <body class="d-flex flex-column h-100">
    <header>
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-xl">
          <a class="navbar-brand" href="#">Status</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Home</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>
    <main class="flex-shrink-0">
      <div class="container">
        <h1 class="mt-5">Service status</h1>
        <p class="lead">Current information pulled from Zabbix.</p>
        <p>
          <?php foreach($zabbixServices as $zabbixService) { ?>
          <div class="card mt-2 mr-2 mb-4 ml-2" >
            <div class="card-header">
              <span><?php printf($zabbixService["name"]); ?></span>
              <?php if($zabbixService["status"] == -1) { ?>
              <span class="float-end text-success">Operational&nbsp;<i class="bi bi-check-circle"></i></span>
              <?php } else if($zabbixService["status"] >= 2 && $zabbixService["status"] <= 3) { ?>
              <span class="float-end text-warning">Degraded&nbsp;<i class="bi bi-exclamation-circle"></i></span>
              <?php } else if($zabbixService["status"] >= 4) { ?>
              <span class="float-end text-danger">Outage&nbsp;<i class="bi bi-x-circle"></i></span>
              <?php } ?>
            </div>
            <div class="card-body">
              <div class="row">
                  <?php
                    $zabbixSlas = $zabbixApi->call('sla.get', array('output' => 'extend', 'serviceids' => $zabbixService['serviceid']));
                    $zabbixSli = $zabbixApi->call('sla.getsli', array('periods' => '29', 'slaid' => $zabbixSlas[0]['slaid']));
                    $zabbixSliPeriods = $zabbixSli['periods'];
                    $zabbixSliSlis = $zabbixSli['sli'];
                  ?>
                  <?php
                    $index = 0;
                    foreach($zabbixSliSlis as $zabbixSliSli) {
                  ?>
                  <div
                    class="col <?php printf(getColBlockStyleForIndex($index)); ?> mx-1 <?php printf(getColBlockColorFor($zabbixSliSli[0]['sli'], $zabbixSlas[0]['slo'])); ?>"
                    style="min-height: 20px; height: 20px;"
                    data-bs-toggle="popover"
                    data-bs-placement="bottom"
                    data-bs-trigger="hover focus"
                    data-bs-title="<?php printf(date("D M j G:i:s T Y", $zabbixSliPeriods[$index]["period_from"])); ?>"
                    data-bs-content="Uptime: <?php printf($zabbixSliSli[0]['sli']); ?>%"
                  ></div>
                  <?php 
                    $index++;
                    }
                  ?>
              </div>
              <div class="row pt-2">
                <div class="d-none d-xl-block col-xl-2 text-muted text-start">28 days ago</div>
                <div class="d-none d-lg-block d-xl-none col-lg-2 text-muted text-start">21 days ago</div>
                <div class="d-none d-md-block d-lg-none col-md-2 text-muted text-start">14 days ago</div>
                <div class="d-block d-md-none col-4 text-muted text-start">7 days ago</div>
                <div class="col-4 col-sm-4 col-md-8 text-muted text-center"><hr/></div>
                <div class="col-4 col-sm-4 col-md-2 text-muted text-end">Today</div>
              </div>
            </div>
          </div>
          <?php } ?>
        </p>
      </div>
    </main>
    <footer class="footer mt-auto py-3 bg-light">
      <div class="container">
        <span class="text-muted"><i class="bi bi-github"></i>&nbsp;<a href="https://github.com/joestrhq/status-page-zabbix" target="_blank">joestrhq/status-page-zabbix</span>
      </div>
    </footer>
  </body>
</html>

