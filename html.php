<?php
// the little bit of php code here is used to decide which script(s) and how much to send to the browser
$full = empty($_GET); // 'full' means with user interface and interactivity, otherwise just build the timeline from the json specified in the url
?>
<!DOCTYPE html>
<html lang='en' class='h-100' data-bs-theme="light">

<head>
    <title>Time Line</title>
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD' crossorigin='anonymous'>
    <style>
        /* @media print{@page {size: landscape;}} */
        input[type='color'] {cursor: pointer;}
        .badge.bg-info {position: absolute; top: .375rem; right: 1rem}
    </style>

<?php
if ($full) { // the following is part of the user interface, as needed
    echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css'>";
}
?>

</head>

<body class='bg-body-secondary container h-100 pt-5'>

    <nav class="fixed-top navbar navbar-expand-sm">
        <div class="container">
            <a href="#" class="navbar-brand" id="page_title"></a>
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#whole_menu" aria-controls="whole_menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse invisible" id="whole_menu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            File
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form enctype="multipart/form-data" id="form_csv" data-type="csv" onsubmit="io.upload(event);">
                                    <label for="csv" class="dropdown-item">New from CSV...</label>
                                    <input type="file" accept=".csv" class="d-none" id="csv" name="csv" onchange="document.getElementById('go_csv').click();">
                                    <button type="submit" id="go_csv" class="d-none">Upload</button>
                                </form>
                            </li>
                            <li>
                                <label for="json" class="dropdown-item">Import from JSON...</label>
                                <input type="file" accept=".json" class="d-none" id="json" onchange="document.getElementById('go_json').click();">
                                <button type="submit" id="go_json" class="d-none" data-type="json" onclick="io.upload(event);">Import</button>
                            </li>
                            <li><a href="#" class="dropdown-item disabled" onclick="io.download(event.target);" data-menu="file" data-item="save" download="timeline.json">Export as JSON...</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown" id="view_menu">
                        <a class="nav-link dropdown-toggle disabled" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            View
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Title</h6></li>
                            <li><div class="dropdown-item"><input type="text" class="form-control" id="custom_title" maxlength="32" oninput="memory.title(event.target.value);" onpaste="memory.title(event.target.value);"></div></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="#" class="dropdown-item" onclick="menu.toggle(event.target);" data-menu="options" data-checked="false" data-item="hide"   data-label="Show row labels">Hide row labels</a></li>
                            <li><a href="#" class="dropdown-item" onclick="menu.toggle(event.target);" data-menu="options" data-checked="false" data-item="nosort" data-label="Sort rows">Don't sort rows</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Colors</h6></li>
                            <li><a href="#" class="dropdown-item" onclick="menu.toggle(event.target);" data-menu="options" data-checked="false" data-item="same"   data-label="Multi colors per row">Single color per row&nbsp;</a></li>
                            <li>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="dropdown-item pe-1" onclick="menu.toggle(event.target);" data-menu="options" data-checked="false" data-item="single" data-label="Multi colors for all:">Single color for all:</a>
                                    <input class="ms-1 me-3" type="color" id="color_picker" value="#88dd88" oninput="table.transform();">
                                </div>
                            </li>
                            <li><a href="#" class="dropdown-item" onclick="menu.toggle(event.target);" data-menu="theme" data-checked="false" data-item="theme" data-label="Light mode">Dark mode</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Dimensions</h6></li>
                            <li><a href="#" class="dropdown-item" onclick="menu.toggle(event.target);" data-menu="options" data-checked="false" data-item="expand" data-label="Collapse rows">Expand rows</a></li>
                            <li><a href="#" class="dropdown-item" onclick="menu.toggle(event.target);" data-menu="options" data-checked="false" data-item="wider"  data-label="Shrink timeline">Widen timeline</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Rows</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="#" class="dropdown-item" onclick="menu.reset();">Reset to defaults</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#help" class="nav-link" data-bs-toggle="offcanvas"><i class="bi bi-question-circle"></i></a>
                    </li>
            </ul>
            </div>
        </div>
    </nav>

    <main class="h-100 py-2 overflow-x-scroll" id="timeline"></main>

<?php
if ($full) {
    require("help.php");
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>';
}
echo "<script src='script.js'></script>";
echo "<script>";
if (!$full) {
    echo "memory.fullUI = false;";
    echo "document.body.classList.remove('bg-body-secondary');";
    echo "document.body.classList.add('bg-transparent');";
}
echo "timeline.load();";
echo "</script>";
?>

</body>
</html>
