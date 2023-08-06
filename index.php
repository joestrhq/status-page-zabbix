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
          <div class="card mt-2 mr-2 mb-2 ml-2" >
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
              <p>
                <?php
                  $zabbixSlas = $zabbixApi->call('sla.get', array('output' => 'extend', 'serviceids' => $zabbixService['serviceid']));
                  $zabbixSlis = $zabbixApi->call('sla.getsli', array('periods' => '1', 'slaid' => $zabbixSlas[0]['slaid']));
                ?>
                <span>Uptime</span>
                <div class="progress" role="progressbar" aria-label="Basic example" aria-valuenow="<?php printf($zabbixSlis['sli'][0][0]['sli'])?>" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar" style="width: <?php printf($zabbixSlis['sli'][0][0]['sli'])?>%"><?php printf($zabbixSlis['sli'][0][0]['sli'])?>%</div>
                </div>
              </p>
            </div>
          </div>
          <?php } ?>
        </p>
      </div>
    </main>
    <footer class="footer mt-auto py-3 bg-light">
      <div class="container">
        <span class="text-muted">Just a sticky footer ... ignore it.</span>
      </div>
    </footer>
  </body>
</html>

