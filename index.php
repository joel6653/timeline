<?php
// this php is the main point-of-entry to the app, but merely decides what gets sent back to the browser
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

function csv_to_json($file_name) { // called from this file (above), as well as from several points in script.php
    $input = array_map("str_getcsv", file($file_name)); // convert csv input file to array
    // echo $input;
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
        // $end = array_shift($value);
        // $row[] = empty($end) ? false : "new Date(" . strtotime($end) * 1000 . ")";
        $output[] = $row; // add row to array
    }
    // $typed_headings = [];
    // $typed_headings[0] = ["label" => $headings[0], "type" => "string"];
    // $typed_headings[1] = ["label" => $headings[1], "type" => "string"];
    // $typed_headings[2] = ["label" => $headings[2], "type" => "date"];
    // $typed_headings[3] = ["label" => $headings[3], "type" => "date"];
    array_unshift($output, $headings); // put back the headings which were removed above
    return json_encode($output);
}
?>