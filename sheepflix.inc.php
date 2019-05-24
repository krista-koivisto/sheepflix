<?php
    $playing = 0;
    $movies = array();

    function GetShows()
    {
        global $searchDirectory;

        if (!isset($searchDirectory))
        {
            $searchDirectory = "sheepdrive";
        }

        $entries = array();

        if ($handle = opendir($searchDirectory))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != '.' && $entry != '..' && is_dir($searchDirectory . '/' . $entry))
                {
                    $entries[] = $entry;
                }
            }

            closedir($handle);
        }
        else
        {
            die("There is currently nothing to show on Sheepflix, I'm afraid. Come baahck later!");
        }

        return $entries;
    }

    function GetEpisodes($shows)
    {
        global $searchDirectory;

        if (!isset($searchDirectory))
        {
            $searchDirectory = "sheepdrive";
        }

        $episodeList = array();

        foreach ($shows as $show)
        {
            $files = array();

            if ($handle = opendir($searchDirectory . '/' . $show))
            {
                while (false !== ($entry = readdir($handle)))
                {
                    if (!is_dir($searchDirectory . '/' . $show . '/' . $entry))
                    {
                        $files[] = $entry;
                    }
                }

                sort($files);

                foreach ($files as $entry)
                {
                    // Get subtitles
                    if ($handle = opendir($searchDirectory . '/' . $show . '/subtitles'))
                    {
                        $episode_file = substr($entry, 0, -4);

                        while (false !== ($sub_entry = readdir($handle)))
                        {
                            if (!is_dir($searchDirectory . '/' . $show . '/subtitles/' . $sub_entry))
                            {
                                if ($episode_file == substr($sub_entry, 0, -4))
                                {
                                    $sub_files[] = $sub_entry;
                                }
                                else if ($show == substr($sub_entry, 0, -4))
                                {
                                    $sub_files[] = $sub_entry;
                                }
                            }
                        }
                    }

                    if ($entry != '.' && $entry != '..' && $entry != 'logo.jpg' && !is_dir($searchDirectory . '/'.$entry) &&
                        substr($entry, 0, 3) != '[M]')
                    {
                        preg_match('/(.*?) S([0-9])+E([0-9]+) (.*?)\.[mkv|mp4]/', $entry, $data);
                        $show_name = $data[1];
                        $season = $data[2];
                        $episode = $data[3];
                        $title = $data[4];

                        $episodeList[$show]['is_series'][] = true;
                        $episodeList[$show]['show'][] = $show_name;
                        $episodeList[$show]['title'][] = $title;
                        $episodeList[$show]['season'][] = $season;
                        $episodeList[$show]['episode'][] = $episode;
                        $episodeList[$show]['subtitles'][] = $sub_files[0]; // TODO: support for multiple sub files
                        $episodeList[$show]['file'][] = $entry;
                        $episodeList[$show]['directory'][] = $show;

                        // Clear sub files
                        $sub_files = array();
                    }
                    else if (substr($entry, 0, 3) == '[M]')
                    {
                        AddMovie($show . "/" . $entry, $show . "/subtitles/" . $sub_files[0], md5($entry), substr($entry, 4, strlen($entry) - 8));
                    }
                }

                closedir($handle);
            }
        }

        return $episodeList;
    }

    function AddShow($data)
    {
        $show = $data['show'][0];
        $directory = $data['directory'][0];

        for ($i = 0; $i < count($data['file']); $i++)
        {
            if ($data['is_series'] == true)
            {
                $filename = $data['file'][$i];
                $title = $data['title'][$i];
                $subtitles = $data['subtitles'][$i];
                $season = number_format($data['season'][$i]);
                $episode = number_format($data['episode'][$i]);
                $id = md5($filename);

                AddMovie($directory . "/" . $filename, $directory . "/subtitles/" . $subtitles, $id, $show, 720, 2010, true, $season, $episode, $title);
            }
        }
    }

    function AddMovie($filename, $subtitles = '', $id, $name = "(Unnamed)", $width = 1080, $year = 2000, $is_series = false, $season = 0, $episode = 0, $title = "(Untitled)")
    {
        global $movies;

        $movie = array(
                        "is_series" => $is_series,
                        "id" => $id,
                        "name" => $name,
                        "year" => $year,
                        "width" => $width,
                        "season" => $season,
                        "episode" => $episode,
                        "subtitles" => $subtitles,
                        "title" => $title,
                        "file" => $filename
                      );

        $movies[] = $movie;
    }

    function PlayMovie($name)
    {
        global $movies, $playing;

        $playing = array_search($name, array_column($movies, 'id'));
    }

    function GetMovie()
    {
        global $movies, $playing;

        return $movies[$playing];
    }

    $shows = GetShows();
    $episodes = GetEpisodes($shows);

    foreach ($episodes as $episode)
    {
        AddShow($episode);
    }

    if (isset($_GET['sheep']))
    {
        PlayMovie($_GET['sheep']);

        if (!isset($playing) || !is_numeric($playing))
        {
            die("Failed to find any sheep by that ID! :(");
        }
    }
?>
