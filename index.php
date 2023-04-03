<?php
// this php is like the main point-of-entry to the app, but merely decides what gets sent back to the browser
switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET": // render the full app, either in help mode or in show-a-timeline mode
        require("html.php");
        break;
    case "POST": // a csv file has been uploaded, so we convert it to json and send it back to the requesting page
        echo csv_to_json($_FILES["csv"]["tmp_name"]);
        break;
    default:
        die("How did you get here?");
        break;
}

function csv_to_json($file_name) { // called from this file (above), as well as from script.php and simple_script.php
    $input = array_map("str_getcsv", file($file_name)); // convert csv input file to array
    $headings = array_shift($input); // temporarily remove headings in the first row
    foreach ($input as $key => $value) { // process all the other rows, one-by-one
        $row = []; // start with empty row array
        $row[] = array_shift($value); // row label
        if (count($headings) == 4) {
            $row[] = array_shift($value); // bar label, if supplied
        }
        $row[] = strtotime(array_shift($value)) * 1000; // start date
        $end = strtotime(array_shift($value)); // end date
        $row[] = empty($end) ? $end : $end * 1000; // either a valid unix timestamp or blank
        $output[] = $row; // add row to array
    }
    array_unshift($output, $headings); // put back the headings which were removed above
    return json_encode($output);
}
?>