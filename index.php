<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>AWS Test Web Server Application</title>

    <link rel="stylesheet"
          href="css/screen.css"
          type="text/css"
          media="screen"
          title="default" />
</head>

<body>

<?php

/*
 * Get EC2 Instance Metadata
 * IMDSv2 is used for security.
 */

$token = @file_get_contents(
    "http://169.254.169.254/latest/api/token",
    false,
    stream_context_create([
        "http" => [
            "method" => "PUT",
            "header" => "X-aws-ec2-metadata-token-ttl-seconds: 21600"
        ]
    ])
);

$metadataOptions = [
    "http" => [
        "method" => "GET",
        "header" => "X-aws-ec2-metadata-token: " . $token
    ]
];

$metadataContext = stream_context_create($metadataOptions);

/* Instance ID */
$instanceId = @file_get_contents(
    "http://169.254.169.254/latest/meta-data/instance-id",
    false,
    $metadataContext
);

/* Private IP */
$privateIp = @file_get_contents(
    "http://169.254.169.254/latest/meta-data/local-ipv4",
    false,
    $metadataContext
);

/* Availability Zone */
$availabilityZone = @file_get_contents(
    "http://169.254.169.254/latest/meta-data/placement/availability-zone",
    false,
    $metadataContext
);


/*
 * Stress the system for a maximum of 10 minutes.
 * Kill all stress processes when requested by the user.
 */

$stressOrKill = isset($_GET["stress"]) ? $_GET["stress"] : "";

if (strlen($stressOrKill) > 0) {

    if ($stressOrKill == "start") {

        echo("<h2>Generating load</h2>");

        exec(
            "stress --cpu 4 --io 1 --vm 1 --vm-bytes 128M --timeout 600s > /dev/null 2>/dev/null &"
        );

    } elseif ($stressOrKill == "stop") {

        exec("pkill -9 stress");

        echo("<h2>Killed stress processes</h2>");
    }
}

?>

<!-- start content -->

<div id="content">

    <center>

        <img src="images/AWS_Logo_Web_200px.png"
             alt="AWS Logo" />

        <br />
        <br />

        <h2>EC2 Instance Information</h2>

        <table border="1"
               width="50%"
               cellpadding="8"
               cellspacing="0">

            <tr>
                <td><strong>Instance ID</strong></td>
                <td>
                    <?php echo htmlspecialchars(trim($instanceId)); ?>
                </td>
            </tr>

            <tr>
                <td><strong>Private IP Address</strong></td>
                <td>
                    <?php echo htmlspecialchars(trim($privateIp)); ?>
                </td>
            </tr>

            <tr>
                <td><strong>Availability Zone</strong></td>
                <td>
                    <?php echo htmlspecialchars(trim($availabilityZone)); ?>
                </td>
            </tr>

        </table>

        <br />
        <br />

        <h2>Generate Load</h2>

        <table border="0"
               width="30%"
               cellpadding="0"
               cellspacing="0"
               id="content-table">

            <tr>

                <td>
                    <form action="index.php" method="get">
                        <input type="hidden"
                               name="stress"
                               value="start" />

                        <input type="submit"
                               value="Start Stress" />
                    </form>
                </td>

                <td>
                    <form action="index.php" method="get">
                        <input type="hidden"
                               name="stress"
                               value="stop" />

                        <input type="submit"
                               value="Stop Stress" />
                    </form>
                </td>

            </tr>

        </table>

    </center>

<!-- end content -->

</div>

</body>
</html>