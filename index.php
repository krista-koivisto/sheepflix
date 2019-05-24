<?php
include_once("sheepflix.inc.php");
?>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="//twemoji.maxcdn.com/2/twemoji.min.js?11.0"></script>
    <link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet">
    <link href="css/index.css?v=20180922001" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <title><?php
        $movie = GetMovie();
        echo $movie['name'] . ": " . $movie['title'];
        echo " (S" . sprintf('%02d', $movie['season']) . "E" . sprintf('%02d', $movie['episode']) . ")"; ?> - Sheepflix BETA</title>
</head>
    <body>
        <div class="pageContainer">
            <div class="pageLogo">
                <h3 class="logo"><a href="sheeplist/" style="color: inherit;">SHEEPFLIX</a><span class="logo_sub">BETA</span></h3>
            </div>

            <div class="pageVideo">
                <table id="videoArea" class="videoFrame">
                        <th></th><th></th>
                        <tr class="tdContainer">
                            <td class="videoContainer">
                                <video autoplay muted id="videoPlayer" width="100%" height="auto">
                                    <source src="sheepdrive/<?php echo GetMovie()['file']; ?>" type="video/mp4">
                                    <track src="sheepdrive/<?php echo GetMovie()['subtitles'] . ''; ?>" kind="subtitles" srclang="en" label="English" default="" />
                                        Sorry, your browser does not appear to support HTML5 video. :(
                                </video>
                                <div class="videoControls" id="videoControls">
                                    <span class="videoControlsBG">
                                        <button class="videoControl playPause" type="button" id="playPause"><i id="playPauseImage" class="fas fa-pause"></i></button>
                                        <span class="rangeControls">
                                            <input class="videoControl seekbar" type="range" id="seekbarControl" step="0.00001" value="0">
                                            <input class="videoControl volume" type="range" id="volumeControl" min="0" max="1" step="0.05" value="1">
                                        </span>
                                        <!--<button class="videoControl mute" type="button" id="muteControl">Mute</button>-->
                                        <span id="timeDisplay" class="videoControl timeLeft">0:00</span>
                                        <button class="videoControl fullscreen" type="button" id="fullscreenControl"><i class="fas fa-expand"></i></button>
                                    </span>
                                </div>
                            </td>
                            <td class="chatContainer">
                                    <div class="chatArea">
                                        <div class="chatText">
                                            <div style="border-bottom: 1px dotted #333333; padding-bottom: 8px; margin-bottom: 12px;"><div class="chatMsg" style="line-height: 22px; padding: 20px; background-color: #7f669d33;"><b>Welcome to Sheepflix!</b><br><br>
                                            You are currently watching:<br/><?php
                                                $movie = GetMovie();
                                                if ($movie['season'] != 0)
                                                {
                                                    echo "<b>" . $movie['name'] . "</b> - Season " . $movie['season'] . "<br/><br/><b>Episode " . $movie['episode'] . "</b>: <i>" . $movie['title'] . "</i>.";
                                                }
                                                else
                                                {
                                                    echo "<b>" . $movie['name'] . "</b>.";
                                                }
                                            ?> Enjoy!<br></div></div>
                                        </div>
                                        <div class="chatBox">
                                            <input id="chatName" type="text" value="AnonSheep" maxlength="12" style="float: left; height: 30px; width: 78%; margin-bottom: 4px; display: inline; padding-left: 4px; font-weight: bold; color: #222222;"></input>
                                            <input onclick="updateName();" class="chatNameButton" type="button" value="Set"></input>
                                            <textarea id="chatTextArea" style="float: left; height: 80px; width: 78%;" type="text"></textarea>
                                            <input onclick="sendChat();" class="chatSendButton" type="button" value="Send"></input>
                                        </div>
                                    </div>
                            </td>
                        </tr>
                    </div>
                </table>
            </div>
        </div>
    </body>

    <script>
    var movie = <?php echo json_encode(GetMovie()); ?>;
    var sessionID = "<?php echo $_GET['sheep']; ?>";
    </script>

    <script src="js/sheepflix.js?v=20182209001" type="text/javascript"></script>
    <script src="js/sheepflix_ws.js?v=20182209001" type="text/javascript"></script>
</html>
