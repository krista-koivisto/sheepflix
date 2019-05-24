<?php
$searchDirectory = "../sheepdrive";
include_once("../sheepflix.inc.php");
?>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="//twemoji.maxcdn.com/2/twemoji.min.js?11.0"></script>
    <link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet">
    <link href="../css/index.css?v=20180922001" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <title>Sheepo's List - Sheepflix BETA</title>
</head>
<body>
<div style="display: table; width: 100%;">
    <div class="pageLogo">
        <h3 class="logo">SHEEPFLIX<span class="logo_sub">BETA</span></h3>
    </div>
</div>
<div style="padding-top: 20px; width: 100%;">
<?php
    $currentShow = "";
    $firstShow = true;

    foreach ($movies as $movie)
    {
        $newShow = false;
        $showID = md5($movie['name'] . $movie['season']);

        if ($currentShow != $showID)
        {
            if (!$firstShow)
            {
                echo "</div></div></div>";
            }
            else
            {
                $firstShow = false;
            }

            $directory = $searchDirectory . '/' . substr($movie['file'], 0, strrpos($movie['file'], '/'));
            $logo = $directory . '/logo.jpg';
            $logo_css = "";

            if (file_exists($logo))
            {
                $logo_css = ' background: url("' . $logo . '") no-repeat center; background-size: cover; background-color: rgba(19, 19, 19, 0.9); background-blend-mode: multiply;';
            }

            echo "<div style='margin: 20px 60px; text-align: left;'>";
            //echo "<div style='max-width: 960px; margin: 20px auto; padding: 20px 40px; background-color: #13131366; border: 4px solid #29242e; border-radius: 18px;" . $logo_css . "'>";
            echo "<div style='max-width: 980px; margin: 20px auto; padding: 20px 40px; background-color: #13131366; border: 10px solid #666666; border-radius: 18px;" . $logo_css . "'>";
            echo "<div style='margin: 0 0 20px 0; padding: 12px; border-radius: 24px; text-align: center; text-transform: uppercase; font-size: 28px; color: #DDDDDD; font-weight: bold; font-family: \"Abel\", serif;'>";
            echo "<span style='display: inline-block; width: 90%; background: linear-gradient(to right, transparent, #DDDDDD88, transparent); border-radius: 48px; overflow: hidden; margin-bottom: 12px; padding: 12px 20px;'>" . $movie['name'] . "</span><br/>";
            if ($movie['is_series'] == true)
            {
                echo "<span style='display: inline-block; line-height: 24px; font-size: 24px; width: 70%; border-image: linear-gradient(to right, transparent, #DDDDDD55, transparent); background: linear-gradient(to right, transparent, #DDDDDD55, transparent); border-image-slice: 1; border-top: 1px solid transparent; border-bottom: 1px solid transparent; padding: 8px 0 8px 0; opacity: 0.7;'>Season " . $movie['season'] . "</span>";
            }
            echo "</div>";
            echo "<div style='background-color: #00000044; border-radius: 12px; padding: 12px;'>";

            $currentShow = $showID;
            $newShow = true;
        }

        if ($movie['is_series'] == true)
        {
            echo "<a href='../?sheep=" . $movie['id'] . "'>";
            echo "<div class='showListItem'>";
            echo "<span style='color: white; position: relative; bottom: 40px; left: 26px; width: 0px; opacity: 0.6; font-size: 12px;'>episode</span>";
            echo "<div style='border-radius: 9px 0 0 9px; color: white; line-height: 100px; width: 180px; height: 100%; border-right: 1px dotted #DDDDDD44; background-color: #4a3e57dd; font-size: 40px; font-weight: bold;'>" . $movie['episode'] . "</div><br/>";
            echo "<span style='display: inline-block; width: 100%; padding: 12px; font-weight: bold; opacity: 0.9;'>" . $movie['title'] . "</span>";
            echo "</div>";
            echo "</a>";
        }
        else
        {
            echo "<a href='../?sheep=" . $movie['id'] . "'>";
            echo "<div class='showListItem'>";
            echo "<span style='color: white; position: relative; bottom: 40px; left: 26px; width: 0px; opacity: 0.6; font-size: 12px;'></span>";
            echo "<div style='border-radius: 9px 0 0 9px; color: white; line-height: 100px; width: 180px; height: 100%; border-right: 1px dotted #DDDDDD44; background-color: #4a3e57dd; font-size: 40px; font-weight: bold;'>M</div><br/>";
            echo "<span style='display: inline-block; width: 100%; padding: 12px; font-weight: bold; opacity: 0.9;'>" . $movie['name'] . "</span>";
            echo "</div>";
            echo "</a>";
        }
    }
?>
</div>
</body>
</html>
