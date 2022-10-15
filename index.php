<?php

require('vendor/autoload.php');

use IntelliTrend\Zabbix\ZabbixApi;

include_once './config.sample.php';

$zabbixApiOptions = array('sslVerifyPeer' => true, 'sslVerifyHost' => true);
$zabbixApi = new ZabbixApi();

try {
	$zabbixApi->loginToken($zabbixApiServerUri, $zabbixApiAccessToken, $zabbixApiOptions);
	$result = $zabbixApi->call('service.get', array("output" => "extend", "sortfield" => "sortorder"));
} catch (Exception $ex) {
	print "==== Exception ===\n";
	print 'ErrorCode: '.$ex->getCode()."\n";
	print 'ErrorMessage: '.$ex->getMessage()."\n";
	exit;
}

?>
<html>
  <head>
    <link href="https://assets.x3cdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://assets.x3cdn.com/bootstrap-icons/1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      main > .container {
        padding-top: 60px;
      }
    </style>
    <script src="https://assets.x3cdn.com/bootstrap/5.1.3/js/bootstrap.bundle.js"></script>
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
          <table class="table">
            <thead>
              <th>Sort order</th>
              <th>Service ID</th>
              <th>Name</th>
              <th>Status</th>
            </thead>
            <tbody>
              <?php foreach($result as $resultRow) { ?>
              <?php if($resultRow["status"] == -1) { ?>
              <tr class="table-success">
                <td><?php printf($resultRow["sortorder"]); ?></td>
                <td><?php printf($resultRow["serviceid"]); ?></td>
                <td><?php printf($resultRow["name"]); ?></td>
                <td><i class="bi bi-check-circle"></i></td>
              <?php } else if($resultRow["status"] == 2) { ?>
              <tr class="table-danger">
                <td><?php printf($resultRow["sortorder"]); ?></td>
                <td><?php printf($resultRow["serviceid"]); ?></td>
                <td><?php printf($resultRow["name"]); ?></td>
                <td><i class="bi bi-exclamation-circle"></i></td>
              <?php } else if($resultRow["status"] > 4) { ?>
              <tr class="table-danger">
                <td><?php printf($resultRow["sortorder"]); ?></td>
                <td><?php printf($resultRow["serviceid"]); ?></td>
                <td><?php printf($resultRow["name"]); ?></td>
                <td><i class="bi bi-x-circle"></i></td>
              <?php } ?>
              </tr>
              <?php } ?>
            </tbody>
          </table>
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
