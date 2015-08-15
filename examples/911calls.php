<?php

// PhpSoda is packaged up in a Phar archive which allows us to include the entire library with a single "include."
// You may also use "include_once," "require," or "require_once"
include "phpsoda-0.1.0.phar";

// PhpSoda organizes its code inside namespaces; in order to use PhpSoda, you'll have to "use" the namespaces. These
// three namespaces should suffice for most code.
use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlQuery;

// If someone has pushed the "Submit" button, this'll be set to true
$postBack = $_SERVER['REQUEST_METHOD'] == 'POST';

if ($postBack)
{
    // Our client will store information about the host, token, and authentication (if necessary)
    $sc = new SodaClient("data.seattle.gov", "B0ixMbJj4LuQVfYnz95Hfp3Ni");
    $ds = new SodaDataset($sc, "pu5n-trf4");

    // Build a SoqlQuery with functions
    $soql = new SoqlQuery();
    $soql->where("within_circle(incident_location, {$_POST['latitude']}, {$_POST['longitude']}, {$_POST['range']})")
         ->limit(20);

    // Get the dataset. $results is now an associative array in the same format as the JSON object
    $results = $ds->getDataset($soql);
}

?>

<html>
    <head>
        <title>Seattle Police Department 911 Incident Response</title>
    </head>
    <body>
        <h1>Seattle Police Department 911 Incident Response</h1>

        <?php if (!$postBack) { ?>
            <form method="POST">
                <p>Query for all of the Seattle Fire 911 Calls calls within 500 meters of the Socrata offices in Seattle:</p>
                <p>Try 47.59815, -122.334540 with a range of 500 meters</p>

                <label for="latitude">Latitude</label>
                <input type="text" name="latitude" size="10" value="47.59815"/>
                <br/>

                <label for="longitude">Longitude</label>
                <input type="text" name="longitude" size="10" value="-122.334540"/>
                <br/>

                <label for="range">Range</label>
                <input type="text" name="range" size="10" value="500"/>
                <br/>

                <input type="submit" value="Submit"/>
            </form>
        <?php } else { ?>
            <h2>Results</h2>

            <?php // Create a table for our actual data ?>
            <table border="1">
                <tr>
                    <th>Clearance Time</th>
                    <th>Address</th>
                </tr>

                <?php // Loop through all of the results from the dataset ?>
                <?php foreach($results as $row) { ?>
                    <tr>
                        <?php $timestamp = new DateTime($row["event_clearance_date"]); ?>
                        <td><?= $timestamp->format('Y-m-d h:ia') ?></td>
                        <td><?= $row["hundred_block_location"] ?></td>
                    </tr>
                <?php } ?>
            </table>

            <h3>Raw Response</h3>
            <pre><?= var_dump($results) ?></pre>
        <?php } ?>
    </body>
</html>
