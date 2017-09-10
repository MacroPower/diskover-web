<?php
require __DIR__ . '/../vendor/autoload.php';
use diskover\Constants;
error_reporting(E_ALL ^ E_NOTICE);
require __DIR__ . "/../src/diskover/Diskover.php";

if (!empty($_GET['path'])) {
  $path = $_GET['path'];
	// remove any trailing slash unless root
	if ($path != "/") {
  	$path = rtrim($path, '/');
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>diskover &mdash; File Tree</title>
	<!--<link rel="stylesheet" href="/css/bootstrap.min.css" media="screen" />
		<link rel="stylesheet" href="/css/bootstrap-theme.min.css" media="screen" />-->
	<link rel="stylesheet" href="/css/bootswatch.min.css" media="screen" />
	<link rel="stylesheet" href="/css/diskover.css" media="screen" />
	<link rel="stylesheet" href="/css/diskover-filetree.css" media="screen" />
	<link rel="stylesheet" href="/css/sunburst.css" media="screen" />
	<link rel="stylesheet" href="/css/breadcrumb.css" media="screen" />
</head>

<body>
	<?php include __DIR__ . "/nav.php"; ?>
	<div class="container" id="error" style="display:none;">
		<div class="row">
			<div class="alert alert-dismissible alert-warning col-xs-8">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>Sorry, no files found, all files too small (filtered) or something else bad happened :(</strong> Choose a different path and try again or check browser console and Elasticsearch for errors.
			</div>
		</div>
	</div>
	<div class="container-fluid" id="mainwindow">
		<div class="row">
			<div class="col-xs-4" id="tree-container">
				<form class="form-inline" id="path-container" style="display:none;">
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="path" id="path" class="path" value="">
						</div>
					</div>
					<button type="submit" id="submit" class="btn btn-primary btn-sm">Go</button>
				</form>
				<div class="buttons-container" id="buttons-container" style="display:none;">
					<div class="btn-group">
						<button class="btn btn-default dropdown-toggle btn-sm" type="button" data-toggle="dropdown">Filter
        <span class="caret"></span></button>
						<ul class="dropdown-menu">
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=1024&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>1 KB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=262144&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>256 KB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=524288&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>512 KB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=1048576&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>1 MB (default)</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=2097152&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>2 MB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=5242880&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>5 MB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=10485760&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>10 MB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=26214400&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>25 MB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=52428800&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>50 MB</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=104857600&maxdepth=<?php if (isset($_GET['maxdepth'])) echo $_GET['maxdepth']; ?>">>100 MB</a></li>
						</ul>
					</div>
					<div class="btn-group">
						<button class="btn btn-default dropdown-toggle btn-sm" type="button" data-toggle="dropdown">Max Depth
        <span class="caret"></span></button>
						<ul class="dropdown-menu">
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=<?php if (isset($_GET['filter'])) echo $_GET['filter']; ?>&maxdepth=3">3 (default)</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=<?php if (isset($_GET['filter'])) echo $_GET['filter']; ?>&maxdepth=4">4</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=<?php if (isset($_GET['filter'])) echo $_GET['filter']; ?>&maxdepth=5">5</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=<?php if (isset($_GET['filter'])) echo $_GET['filter']; ?>&maxdepth=8">8</a></li>
							<li><a href="/sunburst.php?path=<?php echo $_GET['path']; ?>&filter=<?php if (isset($_GET['filter'])) echo $_GET['filter']; ?>&maxdepth=10">10</a></li>
						</ul>
					</div>
					<button type="submit" id="reload" class="btn btn-default btn-sm">Reload</button>
				</div>
			</div>
			<div class="col-xs-8" id="sunburst-container" style="display:none;">
				<div class="row">
					<div class="col-xs-4 col-xs-offset-8">
						<div id="sunburst-buttons" class="pull-right">
							<div class="btn-group">
								<button class="btn btn-default dropdown-toggle btn-sm" type="button" data-toggle="dropdown">Hide Thresh
        <span class="caret"></span></button>
								<ul class="dropdown-menu">
									<li><a href="#_self" onclick="changeThreshold(0.001);">0.001</a></li>
									<li><a href="#_self" onclick="changeThreshold(0.005);">0.005</a></li>
									<li><a href="#_self" onclick="changeThreshold(0.01);">0.01</a></li>
									<li><a href="#_self" onclick="changeThreshold(0.05);">0.05</a></li>
									<li><a href="#_self" onclick="changeThreshold(0.1);">0.1 (default)</a></li>
									<li><a href="#_self" onclick="changeThreshold(0.5);">0.5</a></li>
									<li><a href="#_self" onclick="changeThreshold(1);">1</a></li>
								</ul>
							</div>
							<div class="btn-group" data-toggle="buttons">
								<button class="btn btn-default btn-sm" id="size"> Size</button>
								<button class="btn btn-default btn-sm" id="count"> Count</button>
							</div>
							<div id="statustext" class="statustext">
								<span id="statusfilters">
									</span><span id="statushidethresh">
									</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div id="chart"></div>
					<div id="sequence" class="sequence">
						<!-- another version - flat style with animated hover effect -->
						<div class="breadcrumb flat">
						</div>
					</div>
					<div id="legend"></div>
					<div id="explanation">
						<span id="core_top"></span><br/>
						<span id="core_center"></span><br/>
						<span id="core_tag"></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script language="javascript" src="/js/jquery.min.js"></script>
	<script language="javascript" src="/js/bootstrap.min.js"></script>
	<script language="javascript" src="/js/diskover.js"></script>
	<script language="javascript" src="/js/d3.v3.min.js"></script>
	<script language="javascript" src="/js/d3.tip.v0.6.3.js"></script>
	<script language="javascript" src="/js/sunburst.js"></script>
	<script language="javascript" src="/js/spin.min.js"></script>
	<script language="javascript" src="/js/treelist.js"></script>
	<script language="javascript" src="/js/filetree.js"></script>
</body>

</html>